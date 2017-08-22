<?php

namespace Marello\Bundle\PricingBundle;

use Marello\Bundle\PricingBundle\DependencyInjection\Compiler\SubtotalProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloPricingBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SubtotalProviderPass());
        parent::build($container);
    }
}
