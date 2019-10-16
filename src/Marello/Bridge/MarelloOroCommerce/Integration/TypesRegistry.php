<?php

namespace Marello\Bridge\MarelloOroCommerce\Integration;

use Marello\Bundle\UPSBundle\Provider\ChannelType;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry as BaseTypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface as IntegrationInterface;

class TypesRegistry extends BaseTypesRegistry
{
    /**
     * {@inheritdoc}
     */
    public function addChannelType($typeName, IntegrationInterface $type)
    {
        if ($type instanceof ChannelType) {
            return $this;
        }
        return parent::addChannelType($typeName, $type);
    }
}
