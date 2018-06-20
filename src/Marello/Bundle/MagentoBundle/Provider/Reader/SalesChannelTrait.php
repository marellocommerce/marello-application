<?php

namespace Marello\Bundle\MagentoBundle\Provider\Reader;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;

trait SalesChannelTrait
{
    /**
     * @var SalesChannel
     */
    protected $salesChannel;

    /**
     * @param SalesChannel $salesChannel
     * @return $this
     */
    public function setSalesChannel(SalesChannel $salesChannel)
    {
        $this->salesChannel = $salesChannel;

        return $this;
    }
}
