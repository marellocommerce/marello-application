<?php

namespace Marello\Bundle\OrderBundle\Converter;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PaymentBundle\Context\LineItem\Builder\Factory\PaymentLineItemBuilderFactoryInterface;
use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\Factory\PaymentLineItemCollectionFactoryInterface;
use Oro\Bundle\CurrencyBundle\Entity\Price;

class BasicOrderPaymentLineItemConverter implements OrderPaymentLineItemConverterInterface
{
    /**
     * @var PaymentLineItemCollectionFactoryInterface|null
     */
    private $paymentLineItemCollectionFactory = null;

    /**
     * @var PaymentLineItemBuilderFactoryInterface|null
     */
    private $paymentLineItemBuilderFactory = null;

    /**
     * @param null|PaymentLineItemCollectionFactoryInterface $paymentLineItemCollectionFactory
     * @param null|PaymentLineItemBuilderFactoryInterface $paymentLineItemBuilderFactory
     */
    public function __construct(
        PaymentLineItemCollectionFactoryInterface $paymentLineItemCollectionFactory = null,
        PaymentLineItemBuilderFactoryInterface $paymentLineItemBuilderFactory = null
    ) {
        $this->paymentLineItemCollectionFactory = $paymentLineItemCollectionFactory;
        $this->paymentLineItemBuilderFactory = $paymentLineItemBuilderFactory;
    }

    /**
     * @param OrderItem[]|Collection $orderLineItems
     * {@inheritDoc}
     */
    public function convertLineItems(Collection $orderLineItems)
    {
        if (null === $this->paymentLineItemCollectionFactory || null === $this->paymentLineItemBuilderFactory) {
            return null;
        }

        $paymentLineItems = [];
        foreach ($orderLineItems as $orderLineItem) {
            $builder = $this->paymentLineItemBuilderFactory->createBuilder(
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

            $paymentLineItems[] = $builder->getResult();
        }

        return $this->paymentLineItemCollectionFactory->createPaymentLineItemCollection($paymentLineItems);
    }
}
