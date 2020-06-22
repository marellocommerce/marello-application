<?php

namespace Marello\Bundle\OrderBundle;

use Marello\Bundle\OrderBundle\DependencyInjection\Compiler\EmailTwigSandboxConfigurationPass;
use Marello\Bundle\OrderBundle\DependencyInjection\Compiler\OrderItemDataProvidersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloOrderBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new OrderItemDataProvidersPass())
            ->addCompilerPass(new EmailTwigSandboxConfigurationPass());
        parent::build($container);
    }
}
