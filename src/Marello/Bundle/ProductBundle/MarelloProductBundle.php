<?php

namespace Marello\Bundle\ProductBundle;

use Marello\Bundle\ProductBundle\DependencyInjection\Compiler\ProductTypesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloProductBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ProductTypesPass());

        parent::build($container);
    }
}
