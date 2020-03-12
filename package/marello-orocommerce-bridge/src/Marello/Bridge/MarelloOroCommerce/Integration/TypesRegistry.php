<?php

namespace Marello\Bridge\MarelloOroCommerce\Integration;

use Marello\Bundle\UPSBundle\Provider\ChannelType as UpsChannelType;
use Marello\Bundle\PaymentTermBundle\Integration\PaymentTermChannelType;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry as BaseTypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface as IntegrationInterface;

class TypesRegistry extends BaseTypesRegistry
{
    /**
     * {@inheritdoc}
     */
    public function addChannelType($typeName, IntegrationInterface $type)
    {
        if ($type instanceof UpsChannelType || $type instanceof PaymentTermChannelType) {
            return $this;
        }
        return parent::addChannelType($typeName, $type);
    }
}
