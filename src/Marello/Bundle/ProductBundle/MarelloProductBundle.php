<?php

namespace Marello\Bundle\ProductBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\LocaleBundle\DependencyInjection\Compiler\DefaultFallbackExtensionPass;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\DependencyInjection\Compiler\ProductTypesPass;
use Marello\Bundle\ProductBundle\DependencyInjection\Compiler\EmailTwigSandboxConfigurationPass;

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
            ]))
            ->addCompilerPass(new EmailTwigSandboxConfigurationPass());
    }
}
