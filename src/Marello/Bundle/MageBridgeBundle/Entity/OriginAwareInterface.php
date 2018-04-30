<?php

namespace Marello\Bundle\MageBridgeBundle\Entity;

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
