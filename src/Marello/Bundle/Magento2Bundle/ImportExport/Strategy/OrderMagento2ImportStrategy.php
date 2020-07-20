<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Strategy;

use Doctrine\Common\Collections\Criteria;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\Magento2Bundle\Entity\Customer as MagentoCustomer;
use Marello\Bundle\Magento2Bundle\Entity\Order as MagentoOrder;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\TaxBundle\Entity\TaxCode;

class OrderMagento2ImportStrategy extends DefaultMagento2ImportStrategy
{
    /** @var MagentoOrder */
    protected $existingEntity;

    /**
     * {@inheritDoc}
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        if ($entity instanceof MagentoOrder) {
            $this->existingEntity = $entity;
        }

        $existingEntity = parent::findExistingEntity($entity, $searchContext);
        if (null !== $existingEntity) {
            return $existingEntity;
        }

        if ($entity instanceof Order) {
            return $this->tryFindMarelloOrder($entity);
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
     * @param MagentoOrder $entity
     * @return MagentoOrder
     * @throws \Exception
     */
    protected function afterProcessEntity($entity)
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        if (!$entity->getImportedAt()) {
            $entity->setImportedAt($now);
        }
        $entity->setSyncedAt($now);

        /** @var MagentoOrder $order */
        $this->updateOrganizationForNewRelations($entity);
        $this->processOrderItems($entity);
        $this->setCustomerToInnerOrder($entity);
        $this->resetMagentoStoreIfEntityNotFound($entity);
        $this->copyLocalizationInfoFromStoreToOrder($entity);

        $this->existingEntity = null;

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param MagentoOrder $order
     */
    protected function updateOrganizationForNewRelations(MagentoOrder $order): void
    {
        $organization = $order->getChannel()->getOrganization();
        $marelloOrder = $order->getInnerOrder();
        if ($marelloOrder) {
            if (null === $marelloOrder->getId()) {
                $marelloOrder->setOrganization($organization);
            }

            if ($marelloOrder->getBillingAddress() && null === $marelloOrder->getBillingAddress()) {
                $marelloOrder->getBillingAddress()->setOrganization($organization);
            }

            if ($marelloOrder->getBillingAddress() && null === $marelloOrder->getBillingAddress()) {
                $marelloOrder->getBillingAddress()->setOrganization($organization);
            }
        }

        $magentoCustomer = $order->getMagentoCustomer();
        if ($magentoCustomer && $magentoCustomer->getInnerCustomer() &&
            null === $magentoCustomer->getInnerCustomer()->getId()) {
            $magentoCustomer->getInnerCustomer()->setOrganization($organization);
        }
    }

    /**
     * @param MagentoOrder $order
     */
    protected function processOrderItems(MagentoOrder $order): void
    {
        $innerOrder = $order->getInnerOrder();
        if (null === $innerOrder) {
            return;
        }

        foreach ($innerOrder->getItems() as $item) {
            $item->setOrder($innerOrder);
            $item->setOrganization($innerOrder->getOrganization());

            if ($item->getProduct() instanceof Product && $item->getProduct()->getTaxCode() instanceof TaxCode) {
                $item->setTaxCode($item->getProduct()->getTaxCode());
            }
        }
    }

    /**
     * @param MagentoOrder $order
     */
    protected function setCustomerToInnerOrder(MagentoOrder $order): void
    {
        $innerCustomer = $order->getMagentoCustomer();
        if (null === $innerCustomer) {
            return;
        }

        $order->setMagentoCustomerAndFillInnerOrderWithCustomer($innerCustomer);
    }

    /**
     * @param MagentoOrder $order
     */
    protected function resetMagentoStoreIfEntityNotFound(MagentoOrder $order): void
    {
        if (null === $order->getStore()) {
            return;
        }

        /**
         * We won't create new store within order,
         * so in case if it hasn't found we reset it from
         */
        if (null === $order->getStore()->getId()) {
            $order->setStore(null);
        }
    }

    /**
     * @param MagentoOrder $order
     */
    protected function copyLocalizationInfoFromStoreToOrder(MagentoOrder $order): void
    {
        if (null === $order->getStore() || null !== $order->getId() || null === $order->getInnerOrder()) {
            return;
        }

        $order->getInnerOrder()->setLocalization(
            $order->getStore()->getLocalization()
        );

        $order->getInnerOrder()->setLocaleId(
            $order->getStore()->getLocaleId()
        );
    }

    /**
     * @param Order $entity
     * @return Order|null
     */
    protected function tryFindMarelloOrder(Order $entity): ?Order
    {
        if (null === $entity->getId()) {
            return $this->databaseHelper->findOneBy(
                Order::class,
                [
                    'orderNumber' => (string) $entity->getOrderNumber(),
                    'salesChannel' => $entity->getSalesChannel()->getId()
                ]
            );
        }

        return null;
    }

    /**
     * @param MagentoCustomer $entity
     * @return MagentoCustomer|null
     */
    protected function tryFindMagentoCustomer(MagentoCustomer $entity): ?MagentoCustomer
    {
        if (null === $entity->getOriginId() && $entity->getHashId()) {
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
        if (!$entity->getEmail() || !$entity->getFirstName() || !$entity->getLastName()) {
            return null;
        }

        return $this->databaseHelper->findOneBy(
            Customer::class,
            [
                'email' => $entity->getEmail(),
                'firstName' => $entity->getFirstName(),
                'lastName' => $entity->getLastName()
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
