<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Marello\Bundle\OrderBundle\Model\DiscountAwareInterface;
use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;
use Marello\Bundle\PricingBundle\Subtotal\Provider\AbstractSubtotalProvider;

class DiscountSubtotalProvider extends AbstractSubtotalProvider
{
    const TYPE = 'discount';
    const NAME = 'marello.order.subtotals.discount';
    const SUBTOTAL_SORT_ORDER = 100;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param DiscountAwareInterface $entity
     *
     * @return Subtotal|null
     */
    public function getSubtotal($entity)
    {
        if ($this->isSupported($entity)) {
            $amount = $entity->getDiscountAmount();
            $subtotal = new Subtotal([]);
            $subtotal->setLabel($this->translator->trans(self::NAME . '.label'))
                ->setType(self::TYPE)
                ->setSortOrder(self::SUBTOTAL_SORT_ORDER)
                ->setVisible($amount > 0)
                ->setAmount($amount)
                ->setCurrency($this->getBaseCurrency($entity))
                ->setOperation(Subtotal::OPERATION_SUBTRACTION);

            return $subtotal;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($entity)
    {
        return $entity instanceof DiscountAwareInterface;
    }
}
