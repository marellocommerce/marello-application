<?php

namespace Marello\Bundle\SalesBundle\Model;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;

/**
 * Interface SalesChannelAwareInterface
 * @package Marello\Bundle\SalesBundle\Model
 */
interface SalesChannelAwareInterface
{
    /**
     * @return SalesChannel
     */
    public function getSalesChannel();
}
