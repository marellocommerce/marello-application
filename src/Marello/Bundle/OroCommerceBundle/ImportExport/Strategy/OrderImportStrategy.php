<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Strategy\Import\AbstractImportStrategy;

class OrderImportStrategy extends AbstractImportStrategy
{
    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {

        if ($entity instanceof Order) {
            $channel = $this->context->getValue('channel');
            $organization = $channel->getOrganization();
            /** @var SalesChannel $salesChannel */
            $salesChannel = $this->getEntityRepository(SalesChannel::class)
                ->findOneBy(['name' => $channel->getName()]);
            /** @var Order $entity */
            $entity
                ->setSalesChannel($salesChannel)
                ->setSalesChannelName($salesChannel->getName());

            $criteria = [
                'orderReference' => $entity->getOrderReference(),
                'salesChannel' => $entity->getSalesChannel()
            ];
            $order = $this->getEntityByCriteria($criteria, $entity);
            if ($order) {
                $this->strategyHelper->importEntity(
                    $order,
                    $entity,
                    ['id', 'createdAt', 'customer', 'orderNumber', 'data', 'organization']
                );
                $order->setData(array_merge($entity->getData(), $order->getData()) ? : []);
            } else {
                $order = $entity;
            }
            $order->setOrganization($organization);
            $this->processItems($order, $entity);
            $this->processCustomer($order);
            $shippingAddress = $entity->getShippingAddress();
            $primaryAddress = $order->getCustomer()->getPrimaryAddress();
            if ((string)$shippingAddress === (string)$primaryAddress) {
                $order
                    ->setBillingAddress($primaryAddress)
                    ->setShippingAddress($primaryAddress);
            } else {
                foreach ($order->getCustomer()->getAddresses() as $address) {
                    if ((string)$shippingAddress === (string)$address) {
                        $order
                            ->setBillingAddress($address)
                            ->setShippingAddress($address);
                        break;
                    }
                }
            }

            return $this->validateAndUpdateContext($order);
        }
        
        return null;
    }

    /**
     * @param Order $entityToUpdate
     * @param Order $entityToImport
     */
    private function processItems(Order $entityToUpdate, Order $entityToImport)
    {
        foreach ($entityToImport->getItems() as $item) {
            if (!$item->getOrder()) {
                $item->setOrder($entityToUpdate);
            }
        }
    }

    /**
     * @param Order $order
     */
    public function processCustomer(Order $order)
    {
        $entity = $order->getCustomer();
        if ($entity) {
            $criteria = [
                'firstName' => $entity->getFirstName(),
                'lastName' => $entity->getLastName(),
                'email' => $entity->getEmail()
            ];

            /** @var Customer $customer */
            $customer = $this->getEntityByCriteria($criteria, Customer::class);
            $existingPrimaryAddress = null;
            if ($customer) {
                $existingPrimaryAddress = $customer->getPrimaryAddress();
                $this->strategyHelper->importEntity(
                    $customer,
                    $entity,
                    ['id', 'createdAt', 'primaryAddress', 'organization']
                );
            } else {
                $customer = $entity;
            }
            if (!$customer->getOrganization() && $order->getOrganization()) {
                $customer->setOrganization($order->getOrganization());
            }
            $primaryAddress = $entity->getPrimaryAddress();
            $primaryAddress
                ->setCustomer($customer)
                ->setOrganization($customer->getOrganization());

            if (!$existingPrimaryAddress) {
                $customer->setPrimaryAddress($this->processAddress($primaryAddress));
            } else {
                $customer->addAddress($this->processAddress($primaryAddress));
            }

            $em = $this->strategyHelper->getEntityManager(Customer::class);
            $em->persist($customer);
            $em->flush($customer);
            
            $order->setCustomer($customer);
        }
    }

    /**
     * @param MarelloAddress $entity
     * @return MarelloAddress
     */
    public function processAddress(MarelloAddress $entity)
    {
        $criteria = [
            'firstName' => $entity->getFirstName(),
            'lastName' => $entity->getLastName(),
            'postalCode' => $entity->getPostalCode(),
            'city' => $entity->getCity(),
            'street' => $entity->getStreet(),
        ];
        $address = $this->getEntityByCriteria($criteria, MarelloAddress::class);
        if ($address) {
            if ($entity->getOrganization()) {
                $address->setOrganization($entity->getOrganization());
            }
            if ($entity->getCountry()) {
                $address->setCountry($entity->getCountry());
            }
            if ($entity->getRegion()) {
                $address->setRegion($entity->getRegion());
            }
            if ($entity->getPostalCode() && strlen($entity->getPostalCode()) > 0) {
                $address->setPostalCode($entity->getPostalCode());
            }
            if ($entity->getCity() && strlen($entity->getCity()) > 0) {
                $address->setCity($entity->getCity());
            }
            if ($entity->getStreet() && strlen($entity->getStreet()) > 0) {
                $address->setStreet($entity->getStreet());
            }
            if ($entity->getStreet2() && strlen($entity->getStreet2()) > 0) {
                $address->setStreet2($entity->getStreet2());
            }
            if ($entity->getPhone() && strlen($entity->getPhone()) > 0) {
                $address->setPhone($entity->getPhone());
            }
        } else {
            $address = $entity;
        }

        $em = $this->strategyHelper->getEntityManager(MarelloAddress::class);
        $em->persist($address);
        $em->flush($address);

        return $address;
    }
    
    /**
     * @param ContextInterface $context
     */
    public function setImportExportContext(ContextInterface $context)
    {
        $this->context = $context;
    }
    
    /**
     * @param array         $criteria
     * @param object|string $entity object to get class from or class name
     *
     * @return object
     */
    private function getEntityByCriteria(array $criteria, $entity)
    {
        if (is_object($entity)) {
            $entityClass = ClassUtils::getClass($entity);
        } else {
            $entityClass = $entity;
        }
        return $this->getEntityRepository($entityClass)->findOneBy($criteria);
    }
    
    /**
     * @param string $entityName
     *
     * @return EntityRepository
     */
    private function getEntityRepository($entityName)
    {
        return $this->strategyHelper->getEntityManager($entityName)->getRepository($entityName);
    }
    
    /**
     * @param object $entity
     *
     * @return null|object
     */
    private function validateAndUpdateContext($entity)
    {
        // validate entity
        $validationErrors = $this->strategyHelper->validateEntity($entity);
        if ($validationErrors) {
            $this->context->incrementErrorEntriesCount();
            $this->strategyHelper->addValidationErrors($validationErrors, $this->context);
            return null;
        }
        // increment context counter
        if ($entity->getId()) {
            $this->context->incrementUpdateCount();
        } else {
            $this->context->incrementAddCount();
        }
        return $entity;
    }
}
