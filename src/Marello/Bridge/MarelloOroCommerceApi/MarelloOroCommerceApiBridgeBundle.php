<?php

namespace Marello\Bridge\MarelloOroCommerceApi;

use Marello\Bridge\MarelloOroCommerceApi\DependencyInjection\CompilerPass\AddOroProcessorsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloOroCommerceApiBridgeBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddOroProcessorsCompilerPass());
    }
}
