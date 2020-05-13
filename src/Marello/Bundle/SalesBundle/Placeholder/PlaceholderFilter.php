<?php

namespace Marello\Bundle\SalesBundle\Placeholder;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class PlaceholderFilter
{
    public function isSalesChannelPage($entity)
    {
        return $entity instanceof SalesChannel;
    }
}
