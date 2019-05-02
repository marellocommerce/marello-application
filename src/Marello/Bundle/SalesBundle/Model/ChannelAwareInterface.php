<?php

namespace Marello\Bundle\SalesBundle\Model;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;

/**
 * Interface ChannelAwareInterface
 * @package Marello\Bundle\SalesBundle\Model
 */
interface ChannelAwareInterface
{
    /**
     * @return SalesChannel
     */
    public function getSalesChannel();
}
