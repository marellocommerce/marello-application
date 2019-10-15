<?php

namespace Marello\Bundle\ProductBundle;

use Marello\Bundle\ProductBundle\DependencyInjection\Compiler\ProductTypesPass;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\LocaleBundle\DependencyInjection\Compiler\DefaultFallbackExtensionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloProductBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        
        $container
            ->addCompilerPass(new ProductTypesPass())
            ->addCompilerPass(new DefaultFallbackExtensionPass([
                Product::class => [
                    'name' => 'names'
                ]
            ]));
    }
}
