<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CustomerBundle\Entity\Company;
use Marello\Bundle\CustomerBundle\Entity\Customer;
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
            $salesChannel = $this
                ->getEntityRepository(SalesChannel::class)
                ->findOneBy([
                    'integrationChannel' => $channel,
                    'channelType' => $channel->getType()
                ]);

            if (!$salesChannel) {
                $this->context->incrementErrorEntriesCount();
                $this->strategyHelper->addValidationErrors(['No SalesChannel found for Order'], $this->context);
                return null;
            }

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
            $billingAddress = $this->processAddress($entity->getBillingAddress());
            $shippingAddress = $this->processAddress($entity->getShippingAddress());
            $order
                ->setBillingAddress($billingAddress)
                ->setShippingAddress($shippingAddress);

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
            $product = $item->getProduct();
            $order = $item->getOrder();
            $taxCode = $product->getSalesChannelTaxCode($order->getSalesChannel()) ? : $product->getTaxCode();
            $item
                ->setTaxCode($taxCode)
                ->setOrganization($order->getOrganization());
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
            $existingCompany = null;
            if ($customer) {
                $existingPrimaryAddress = $customer->getPrimaryAddress();
                $existingCompany = $customer->getCompany();
                $this->strategyHelper->importEntity(
                    $customer,
                    $entity,
                    ['id', 'createdAt', 'primaryAddress', 'organization', 'company']
                );
            } else {
                $customer = $entity;
            }
            $company = $entity->getCompany();
            if ($company && $existingCompany && $company->getName() === $existingCompany->getName()) {
                $customer->setCompany($existingCompany);
            } elseif ($company) {
                $customer->setCompany($company);
            }
            if (!$customer->getOrganization() && $order->getOrganization()) {
                $customer->setOrganization($order->getOrganization());
            }
            $primaryAddress = $entity->getPrimaryAddress();
            $primaryAddress
                ->setCustomer($customer);

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
                $address->setCompany($entity->getOrganization());
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
        $validationErrors = $this->strategyHelper->validateEntity($entity, null, 'commerce');
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
