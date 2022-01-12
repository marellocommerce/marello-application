<?php

namespace Marello\Bundle\RefundBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Entity\RefundItem;
use Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator;

class RefundTotalsSubscriber implements EventSubscriberInterface
{
    /**
     * @var RefundBalanceCalculator
     */
    protected $balanceCalculator;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'onSubmit'
        ];
    }

    /**
     * {@inheritdoc}
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        /** @var Refund $refund */
        $refund = $event->getData();

        /*
         * Reduce items to sum up the new refund amount
         */
        $refundGrandTotal = 0.00;
        $refundSubTotal = 0.00;
        $refundTaxTotal = 0.00;
        $refund->getItems()->map(function (RefundItem $item) use (
            &$refundSubTotal,
            &$refundTaxTotal,
            &$refundGrandTotal,
            $refund
        ) {
            if ($item->getTaxCode()) {
                $taxTotals = $this->balanceCalculator
                    ->calculateIndividualTaxItem(
                        [
                            'quantity' => $item->getQuantity(),
                            'taxCode' => $item->getTaxCode()->getId(),
                            'refundAmount' => $item->getRefundAmount(),
                        ],
                        $refund
                    );
                $refundSubTotal += (double)$taxTotals->getExcludingTax();
                $refundTaxTotal += (double)$taxTotals->getTaxAmount();
                $refundGrandTotal += (double)$taxTotals->getIncludingTax();
            }
        });

        $refund->setRefundSubtotal($refundSubTotal);
        $refund->setRefundTaxTotal($refundTaxTotal);
        $refund->setRefundAmount($refundGrandTotal);
        $event->setData($refund);
    }

    /**
     * @param RefundBalanceCalculator $balanceCalculator
     */
    public function setRefundBalanceCalculator(RefundBalanceCalculator $balanceCalculator)
    {
        $this->balanceCalculator = $balanceCalculator;
    }
}
