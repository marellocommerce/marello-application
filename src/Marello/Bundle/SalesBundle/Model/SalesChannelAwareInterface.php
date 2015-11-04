<?php

namespace Marello\Bundle\SalesBundle\Model;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;

interface SalesChannelAwareInterface
{
    /**
     * @param SalesChannel $channel
     * @return mixed
     */
    public function addChannel(SalesChannel $channel);

    /**
     * @param SalesChannel $channel
     *
     */
    public function removeChannel(SalesChannel $channel);

    /**
     * @return ArrayCollection
     */
    public function getChannels();
}
