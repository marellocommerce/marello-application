<?php

namespace Marello\Bundle\RefundBundle\Calculator;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Entity\RefundItem;

class RefundBalanceCalculator
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    /**
     * @param Refund $refund
     * @return float
     */
    public function caclulateBalance(Refund $refund)
    {
        $refundsForSameOrder = $this->doctrine
            ->getManagerForClass(Refund::class)
            ->getRepository(Refund::class)
            ->findBy(['order' => $refund->getOrder()]);
        $refundsAmount = $refund->getRefundAmount();
        foreach ($refundsForSameOrder as $prevRefund) {
            if ($refund->getId() !== $prevRefund->getId()) {
                $refundsAmount += $prevRefund->getRefundAmount();
            }
        }

        return $refund->getOrder()->getGrandTotal() - $refundsAmount;
    }

    /**
     * @param Refund $refund
     * @return float
     */
    public function caclulateAmount(Refund $refund)
    {
        $sum = array_reduce($refund->getItems()->toArray(), function ($carry, RefundItem $item) {
            return $carry + $item->getRefundAmount();
        }, 0);

        return $sum;
    }
}
