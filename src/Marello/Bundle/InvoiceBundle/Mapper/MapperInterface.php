<?php

namespace Marello\Bundle\InvoiceBundle\Mapper;

use Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice;

interface MapperInterface
{
    /**
     * @param object $sourceEntity
     * @return AbstractInvoice
     */
    public function map($sourceEntity);
}
