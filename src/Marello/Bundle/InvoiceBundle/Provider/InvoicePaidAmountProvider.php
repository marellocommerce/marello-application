<?php

namespace Marello\Bundle\InvoiceBundle\Provider;

use Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice;

class InvoicePaidAmountProvider
{
    public function getPaidAmount(AbstractInvoice $entity)
    {
        $amount = 0.0;
        foreach ($entity->getPayments() as $payment) {
            $amount += $payment->getTotalPaid();
        }

        return $amount;
    }
}