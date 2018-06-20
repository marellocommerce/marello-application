<?php

namespace Marello\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ProductBundle\Entity\Product;

class OrderImportStrategy extends AbstractImportStrategy
{
    const CONTEXT_ORDER_POST_PROCESS_IDS = 'postProcessOrderIds';

    /**
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        if (!$entity->getUpdatedAt() && $entity->getCreatedAt()) {
            $entity->setUpdatedAt($entity->getCreatedAt());
        }

        $organization = $this->getOrganization($entity->getOrganization()->getId());
        $entity->setOrganization($organization);

        /** @var Order $order */
        $this->processItems($entity);
        $this->processCustomer($entity, $entity->getCustomer());

        $this->appendDataToContext(self::CONTEXT_ORDER_POST_PROCESS_IDS, $entity->getOrderReference());

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param Order $order
     * @param Customer|null $entity
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function processCustomer(Order $order, Customer $entity = null)
    {
        $existingPrimaryAddress = null;

        $manager = $this->doctrineHelper->getEntityRepository(Customer::class);
        $customer = $manager->findOneBy(['email' => $entity->getEmail()]);

        if ($customer) {
            $existingPrimaryAddress = $customer->getPrimaryAddress();
            $this->strategyHelper->importEntity(
                $customer,
                $entity,
                ['id', 'createdAt', 'primaryAddress', 'organization']
            );
        }

        if (!$customer) {
            $customer = $entity;
        }

        $customer->setOrganization($order->getOrganization());

        $primaryAddress = $order->getBillingAddress();

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

    protected function getPrimaryAddress(Customer $customer, MarelloAddress $address)
    {
        $primaryAddress = $customer->getPrimaryAddress();

        if (!$primaryAddress) {
            $primaryAddress = new MarelloAddress();
        }

        $primaryAddress
            ->setCustomer($customer)
            ->setOrganization($customer->getOrganization());

        return $primaryAddress;
    }


    /**
     * @param Order $order
     *
     * @return OrderImportStrategy
     */
    protected function processItems(Order $order)
    {
        foreach ($order->getItems() as $item) {
            $manager = $this->doctrineHelper->getEntityRepository(Product::class);
            $product = $manager->findOneBy(['sku' => $item->getProductSku()]);
            $item->setProduct($product);
            $item->setOrder($order);
        }

        return $this;
    }

    /**
     * @param MarelloAddress $entity
     * @return MarelloAddress
     */
    public function processAddress(MarelloAddress $entity)
    {
        $address = $this->findExistingEntity($entity);
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
     * BC layer to find existing collection items by old identity filed values
     *
     * {@inheritdoc}
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        $existingEntity = parent::findExistingEntity($entity, $searchContext);

        if (!$existingEntity && $entity instanceof Order) {
            $manager = $this->doctrineHelper->getEntityRepository(Order::class);
            $existingEntity = $manager->findOneBy(['orderNumber' => $entity->getOrderNumber()]);
        }

        return $existingEntity;
    }

    private function getOrganization($id)
    {
        $manager = $this->doctrineHelper->getEntityRepository(Organization::class);
        return $manager->findOneBy(['id' => $id]);
    }
}
