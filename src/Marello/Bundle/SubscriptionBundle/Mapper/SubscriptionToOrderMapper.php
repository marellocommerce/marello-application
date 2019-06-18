<?php

namespace Marello\Bundle\SubscriptionBundle\Mapper;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SubscriptionBundle\Entity\Subscription;
use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Oro\Bundle\EntityConfigBundle\Manager\AttributeManager;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class SubscriptionToOrderMapper extends AbstractSubscriptionsMapper
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @param EntityFieldProvider $entityFieldProvider
     * @param PropertyAccessorInterface $propertyAccessor
     * @param AttributeManager $attributeManager
     * @param Registry $doctrine
     */
    public function __construct(
        EntityFieldProvider $entityFieldProvider,
        PropertyAccessorInterface $propertyAccessor,
        AttributeManager $attributeManager,
        Registry $doctrine
    ) {
        parent::__construct($entityFieldProvider, $propertyAccessor, $attributeManager);
        $this->doctrine = $doctrine;
    }

    /**
     * @param Subscription $sourceEntity
     * @return Order
     */
    public function map(Subscription $sourceEntity)
    {
        $order = new Order();
        $data = $this->getData($sourceEntity, Order::class);
        if ($sourceEntity->getSalesChannel()) {
            $data['currency'] = $sourceEntity->getSalesChannel()->getCurrency();
            $this->assignData($order, $data);
            $subscriptionItem = $sourceEntity->getItem();
            if ($subscriptionItem) {
                $product = $this->doctrine
                    ->getManagerForClass(Product::class)
                    ->getRepository(Product::class)
                    ->findOneBy(['sku' => $subscriptionItem->getSku()]);
                if ($product) {
                    $orderItem = new OrderItem();
                    $orderItem
                        ->setProduct($product)
                        ->setQuantity(1)
                        ->setPrice($subscriptionItem->getSpecialPrice() ? : $subscriptionItem->getPrice());
                    $order->addItem($orderItem);
                }
            }
        }

        return $order;
    }
}
