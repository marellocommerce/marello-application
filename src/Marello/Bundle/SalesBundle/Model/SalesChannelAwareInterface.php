<?php

namespace Marello\Bundle\SalesBundle\Model;

use Doctrine\Common\Collections\Collection;
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
     * @return Collection|SalesChannel[]
     */
    public function getChannels();
}
