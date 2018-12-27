<?php

namespace Marello\Bundle\InvoiceBundle\Mapper;

use Marello\Bundle\InvoiceBundle\Entity\Invoice;

interface MapperInterface
{
    /**
     * @param object $sourceEntity
     * @return Invoice[]
     */
    public function map($sourceEntity);
}
