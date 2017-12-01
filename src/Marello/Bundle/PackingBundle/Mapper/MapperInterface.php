<?php

namespace Marello\Bundle\PackingBundle\Mapper;

use Marello\Bundle\PackingBundle\Entity\PackingSlip;

interface MapperInterface
{
    /**
     * @param object $sourceEntity
     * @return PackingSlip[]
     */
    public function map($sourceEntity);
}
