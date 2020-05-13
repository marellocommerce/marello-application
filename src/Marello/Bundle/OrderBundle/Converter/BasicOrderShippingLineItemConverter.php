<?php

namespace Marello\Bundle\OrderBundle\Converter;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ShippingBundle\Context\LineItem\Builder\Factory\ShippingLineItemBuilderFactoryInterface;
use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\Factory\ShippingLineItemCollectionFactoryInterface;
use Oro\Bundle\CurrencyBundle\Entity\Price;

class BasicOrderShippingLineItemConverter implements OrderShippingLineItemConverterInterface
{
    /**
     * @var ShippingLineItemCollectionFactoryInterface|null
     */
    private $shippingLineItemCollectionFactory = null;

    /**
     * @var ShippingLineItemBuilderFactoryInterface|null
     */
    private $shippingLineItemBuilderFactory = null;

    /**
     * @param null|ShippingLineItemCollectionFactoryInterface $shippingLineItemCollectionFactory
     * @param null|ShippingLineItemBuilderFactoryInterface $shippingLineItemBuilderFactory
     */
    public function __construct(
        ShippingLineItemCollectionFactoryInterface $shippingLineItemCollectionFactory = null,
        ShippingLineItemBuilderFactoryInterface $shippingLineItemBuilderFactory = null
    ) {
        $this->shippingLineItemCollectionFactory = $shippingLineItemCollectionFactory;
        $this->shippingLineItemBuilderFactory = $shippingLineItemBuilderFactory;
    }

    /**
     * @param OrderItem[]|Collection $orderLineItems
     * {@inheritDoc}
     */
    public function convertLineItems(Collection $orderLineItems)
    {
        if (null === $this->shippingLineItemCollectionFactory || null === $this->shippingLineItemBuilderFactory) {
            return null;
        }

        $shippingLineItems = [];
        foreach ($orderLineItems as $orderLineItem) {
            $builder = $this->shippingLineItemBuilderFactory->createBuilder(
                $orderLineItem->getQuantity(),
                $orderLineItem
            );

            if (null !== $orderLineItem->getProduct()) {
                $builder->setProduct($orderLineItem->getProduct());
                $builder->setProductSku($orderLineItem->getProduct()->getSku());
                if ($weight = $orderLineItem->getProduct()->getWeight()) {
                    $builder->setWeight($weight);
                }
            }

            if (null !== $orderLineItem->getPrice()) {
                $price = new Price();
                $price
                    ->setValue($orderLineItem->getPrice())
                    ->setCurrency($orderLineItem->getOrder()->getCurrency());
                $builder->setPrice($price);
            }

            $shippingLineItems[] = $builder->getResult();
        }

        return $this->shippingLineItemCollectionFactory->createShippingLineItemCollection($shippingLineItems);
    }
}
