<?php

namespace Marello\Bundle\ShippingBundle\Method\Configuration\Composed;

class ComposedShippingMethodConfigurationBuilderFactory implements
    ComposedShippingMethodConfigurationBuilderFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createBuilder()
    {
        return new ComposedShippingMethodConfigurationBuilder();
    }
}
