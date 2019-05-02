<?php

namespace Marello\Bundle\TaxBundle\Mapper;

use Marello\Bundle\TaxBundle\Model\Taxable;

interface TaxMapperInterface
{
    /**
     * @param object $object
     * @return Taxable
     */
    public function map($object);

    /**
     * Return name of class which can be mapped by this mapper
     *
     * @return string
     */
    public function getProcessingClassName();
}
