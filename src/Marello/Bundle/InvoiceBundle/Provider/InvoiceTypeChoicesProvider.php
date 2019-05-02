<?php

namespace Marello\Bundle\InvoiceBundle\Provider;

use Marello\Bundle\InvoiceBundle\Entity\Creditmemo;
use Marello\Bundle\InvoiceBundle\Entity\Invoice;

class InvoiceTypeChoicesProvider
{
    /**
     * {@inheritdoc}
     */
    public function getInvoiceTypes()
    {
        return [
            Invoice::INVOICE_TYPE => Invoice::INVOICE_TYPE,
            Creditmemo::CREDITMEMO_TYPE => Creditmemo::CREDITMEMO_TYPE,
        ];
    }
}
