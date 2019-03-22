<?php

namespace Marello\Bundle\RefundBundle\Calculator;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\RefundBundle\Entity\Refund;

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
}
