<?php

namespace Marello\Bundle\PricingBundle\Subtotal\Provider;

use Marello\Bundle\PricingBundle\Subtotal\Model\LineItemsAwareInterface;
use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;
use Marello\Bundle\ProductBundle\Model\QuantityAwareInterface;
use Oro\Bundle\CurrencyBundle\Entity\PriceAwareInterface;

class LineItemSubtotalProvider extends AbstractSubtotalProvider
{
    const TYPE = 'subtotal';
    const NAME = 'marello.pricing.subtotals.subtotal';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($entity)
    {
        return $entity instanceof LineItemsAwareInterface;
    }

    /**
     * Get line items subtotal
     *
     * @param LineItemsAwareInterface $entity
     *
     * @return Subtotal
     */
    public function getSubtotal($entity)
    {
        $amount = $this->isSupported($entity)
            ? $this->getRecalculatedSubtotalAmount($entity)
            : 0.0;

        return $this->createSubtotal($entity, $amount);
    }

    /**
     * @param object $entity
     * @param float $amount
     * @return Subtotal
     */
    protected function createSubtotal($entity, $amount)
    {
        $subtotal = new Subtotal([]);
        $subtotal->setLabel($this->translator->trans(self::NAME . '.label'));
        $subtotal->setType(self::TYPE);
        $subtotal->setVisible($amount > 0);
        $subtotal->setAmount($amount);
        $subtotal->setCurrency($this->getBaseCurrency($entity));

        return $subtotal;
    }

    /**
     * @param LineItemsAwareInterface $entity
     * @return float
     */
    protected function getRecalculatedSubtotalAmount($entity)
    {
        $subtotalAmount = 0.0;
        foreach ($entity->getItems() as $lineItem) {
            if ($lineItem instanceof PriceAwareInterface) {
                $subtotalAmount += $this->getRowTotal($lineItem);
            }
        }
        
        if (0 !== count($this->dependOnProviders)) {
            foreach ($this->dependOnProviders as $v) {
                /** @var SubtotalProviderInterface $provider */
                $provider = $v['provider'];
                $operation = $v['operation'];
                if ($provider->isSupported($entity)) {
                    $dependAmount = (float)$provider->getSubtotal($entity)->getAmount();
                    if ($operation === Subtotal::OPERATION_ADD) {
                        $subtotalAmount += $dependAmount;
                    } elseif ($operation === Subtotal::OPERATION_SUBTRACTION) {
                        $subtotalAmount -= $dependAmount;
                    }
                    if ($subtotalAmount < 0) {
                        $subtotalAmount = 0.0;
                    }
                }
            }
        }

        return $this->rounding->round($subtotalAmount);
    }

    /**
     * @param PriceAwareInterface $lineItem
     * @return float|int
     */
    public function getRowTotal(PriceAwareInterface $lineItem)
    {
        if (!$lineItem->getPrice()) {
            return 0;
        }

        $rowTotal = $lineItem->getPrice();

        if ($lineItem instanceof QuantityAwareInterface) {
            $rowTotal *= $lineItem->getQuantity();
        }

        return $rowTotal;
    }
}
