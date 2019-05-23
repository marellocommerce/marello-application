<?php

namespace Marello\Bundle\SubscriptionBundle\Mapper;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SubscriptionBundle\Entity\Subscription;
use Marello\Bundle\SubscriptionBundle\Entity\SubscriptionItem;

class OrderToSubscriptionsMapper extends AbstractSubscriptionsMapper
{
    /**
     * @param Order $sourceEntity
     * @return Subscription[]
     */
    public function map(Order $sourceEntity)
    {
        $hasSubscriptionProducts = false;
        foreach ($sourceEntity->getItems() as $orderItem) {
            if ($orderItem->getProduct()->getType() === 'subscription') {
                $hasSubscriptionProducts = true;
                break;
            }
        }
        if ($hasSubscriptionProducts === false) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Order provided to OrderToSubscriptionsMapper has no subscription products',
                    get_class($sourceEntity)
                )
            );
        }
        $subscriptions = [];
        foreach ($sourceEntity->getItems() as $orderItem) {
            if ($orderItem->getProduct()->getType() === 'subscription') {
                $subscriptions[] = $this->mapSubscription($orderItem);
            }
        }

        return $subscriptions;
    }

    /**
     * @param OrderItem $orderItem
     * @return Subscription
     */
    private function mapSubscription(OrderItem $orderItem)
    {
        $order = $orderItem->getOrder();
        $product = $orderItem->getProduct();
        $subscription = new Subscription();
        $data = $this->getData($order, Subscription::class);
        $duration = $product->getSubscriptionDuration()->getId();
        $purchaseDate = $order->getPurchaseDate() ? : $order->getCreatedAt();
        $startDate = clone $purchaseDate;
        $startDate = $startDate->modify('first day of next month');
        $terminationDate = clone $startDate;
        $terminationDate = $terminationDate->modify(sprintf('+%d month', (int)$duration));

        $data['startDate'] = $startDate;
        $data['terminationDate'] = $terminationDate;
        $data['paymentFreq'] = $product->getPaymentTerm()->getId();
        $data['duration'] = $product->getSubscriptionDuration();
        $data['item'] = $this->mapSubscriptionItem($product, $order->getSalesChannel());
        $this->assignData($subscription, $data);

        return $subscription;
    }

    /**
     * @param Product $product
     * @param SalesChannel $salesChannel
     * @return SubscriptionItem
     */
    private function mapSubscriptionItem(Product $product, SalesChannel $salesChannel)
    {
        $subscriptionItem = new SubscriptionItem();
        $assembledPrice = $product->getSalesChannelPrice($salesChannel) ? :
            $product->getPrice($salesChannel->getCurrency());

        $data = [
            'sku' => $product->getSku(),
            'price' => $assembledPrice->getDefaultPrice()->getValue(),
            'duration' => $product->getSubscriptionDuration(),
            'specialPrice' => $assembledPrice->getSpecialPrice() ? $assembledPrice->getSpecialPrice()->getValue() : null,
            'special_price_duration' => $product->getSpecialPriceDuration()
        ];
        $this->assignData($subscriptionItem, $data);

        return $subscriptionItem;
    }
}