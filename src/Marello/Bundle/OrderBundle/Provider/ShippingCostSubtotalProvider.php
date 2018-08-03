<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;
use Marello\Bundle\PricingBundle\Subtotal\Provider\AbstractSubtotalProvider;

class ShippingCostSubtotalProvider extends AbstractSubtotalProvider
{
    const TYPE = 'shipping_cost';
    const NAME = 'marello.order.subtotals.shipping_cost';
    const SUBTOTAL_SORT_ORDER = 100;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param Order $entity
     *
     * @return Subtotal|null
     */
    public function getSubtotal($entity)
    {
        if ($this->isSupported($entity)) {
            $amount = $entity->getShippingAmountInclTax();
            $subtotal = new Subtotal([]);
            $subtotal->setLabel($this->translator->trans(self::NAME . '.label'))
                ->setType(self::TYPE)
                ->setSortOrder(self::SUBTOTAL_SORT_ORDER)
                ->setVisible($amount > 0)
                ->setAmount($amount)
                ->setCurrency($this->getBaseCurrency($entity))
                ->setOperation(Subtotal::OPERATION_ADD);

            return $subtotal;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($entity)
    {
        return $entity instanceof Order;
    }
}
