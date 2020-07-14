<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Strategy;

use Doctrine\Common\Collections\Criteria;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\Magento2Bundle\Entity\Customer as MagentoCustomer;
use Marello\Bundle\Magento2Bundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;

class OrderMagento2ImportStrategy extends DefaultMagento2ImportStrategy
{
    /** @var Order */
    protected $existingEntity;

    /**
     * {@inheritDoc}
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        $existingEntity = parent::findExistingEntity($entity, $searchContext);
        if (null !== $existingEntity) {
            return $existingEntity;
        }

        if ($entity instanceof MagentoCustomer) {
            return $this->tryFindMagentoCustomer($entity);
        }

        if ($entity instanceof Customer) {
            return $this->tryFindCustomer($entity);
        }

        if ($entity instanceof MarelloAddress) {
            return $this->tryFindAddress($entity);
        }

        if ($entity instanceof OrderItem) {
            return $this->tryFindOrderItem($entity);
        }

        return null;
    }

    /**
     * @param Order $entity
     * @return Order
     * @throws \Exception
     */
    protected function afterProcessEntity($entity)
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        if (!$entity->getImportedAt()) {
            $entity->setImportedAt($now);
        }
        $entity->setSyncedAt($now);

        /** @var Order $order */
        $this->processItems($entity);

        $this->existingEntity = null;

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param Order $order
     *
     * @return self
     */
    protected function processItems(Order $order): self
    {
        $innerOrder = $order->getInnerOrder();

        foreach ($innerOrder->getItems() as $item) {
            $item->setOrder($innerOrder);
        }

        return $this;
    }

    /**
     * @param MagentoCustomer $entity
     * @return MagentoCustomer|null
     */
    protected function tryFindMagentoCustomer(MagentoCustomer $entity): ?MagentoCustomer
    {
        if (null === $entity->getOriginId()) {
            return $this->databaseHelper->findOneBy(
                MagentoCustomer::class,
                [
                    'hashId' => $entity->getHashId(),
                    'channel' => $entity->getChannel()
                ]
            );
        }

        return null;
    }

    /**
     * @param Customer $entity
     * @return Customer|null
     */
    protected function tryFindCustomer(Customer $entity): ?Customer
    {
        return $this->databaseHelper->findOneBy(
            Customer::class,
            [
                'email' => $entity->getEmail(),
                'firstName' => $entity->getFirstName(),
                'lastName' => $entity->getLastName(),
                'channel' => $entity->getChannel()
            ]
        );
    }

    /**
     * @param MarelloAddress $address
     * @return MarelloAddress|null
     */
    protected function tryFindAddress(MarelloAddress $address): ?MarelloAddress
    {
        if (null !== $this->existingEntity->getId()) {
            return null;
        }

        $billingAddress = $this->existingEntity
            ->getInnerOrder()
            ->getBillingAddress();

        if (null !== $billingAddress && $this->isAddressesMatch($address, $billingAddress)) {
            return $billingAddress;
        }

        $shippingAddress = $this->existingEntity
            ->getInnerOrder()
            ->getShippingAddress();

        if (null !== $shippingAddress && $this->isAddressesMatch($address, $shippingAddress)) {
            return $shippingAddress;
        }

        return null;
    }

    /**
     * @param MarelloAddress $importedOrderAddress
     * @param MarelloAddress $existedOrderAddress
     * @return bool
     */
    protected function isAddressesMatch(MarelloAddress $importedOrderAddress, MarelloAddress $existedOrderAddress)
    {
        $isMatched = true;
        $fieldsToMatch = ['street', 'city', 'postalCode', 'country', 'region', 'phone'];
        foreach ($fieldsToMatch as $fieldToMatch) {
            $valueOfImportedEntity = $this->propertyAccessor->getValue($importedOrderAddress, $fieldToMatch);
            $valueOfExistedEntity = $this->propertyAccessor->getValue($existedOrderAddress, $fieldToMatch);
            $isMatched = $isMatched && ($valueOfImportedEntity === $valueOfExistedEntity);
        }

        return $isMatched;
    }

    /**
     * @param OrderItem $orderItem
     * @return OrderItem|null
     */
    protected function tryFindOrderItem(OrderItem $orderItem): ?OrderItem
    {
        if (null !== $this->existingEntity->getId()) {
            return null;
        }

        $criteria = new Criteria(
            Criteria::expr()->eq('productName', $orderItem->getProductName())
        );

        return $this->existingEntity
            ->getInnerOrder()
            ->getItems()
            ->matching($criteria)
            ->first();
    }
}
