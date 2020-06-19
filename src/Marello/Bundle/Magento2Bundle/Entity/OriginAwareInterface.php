<?php

namespace Marello\Bundle\Magento2Bundle\Entity;

interface OriginAwareInterface
{
    /**
     * @param int $originId
     *
     * @return OriginAwareInterface
     */
    public function setOriginId($originId);

    /**
     * @return int
     */
    public function getOriginId();
}
