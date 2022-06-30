<?php

namespace Marello\Bundle\ProductBundle;

use Oro\Bundle\LocaleBundle\DependencyInjection\Compiler\EntityFallbackFieldsStoragePass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
            ->addCompilerPass(new EntityFallbackFieldsStoragePass([
                'Marello\Bundle\ProductBundle\Entity\Product' => [
                    'name' => 'names'
                ]
            ]))
            ->addCompilerPass(new EmailTwigSandboxConfigurationPass());
    }
}
