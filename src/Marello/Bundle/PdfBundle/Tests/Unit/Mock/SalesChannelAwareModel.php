<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Mock;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Model\SalesChannelAwareInterface;

class SalesChannelAwareModel implements
    SalesChannelAwareInterface
{
    /**
     * @var SalesChannel
     */
    protected $salesChannel;

    /**
     * @return SalesChannel
     */
    public function getSalesChannel()
    {
        return $this->salesChannel;
    }

    /**
     * @param SalesChannel $salesChannel
     * @return self
     */
    public function setSalesChannel(SalesChannel $salesChannel)
    {
        $this->salesChannel = $salesChannel;
        
        return $this;
    }
}
