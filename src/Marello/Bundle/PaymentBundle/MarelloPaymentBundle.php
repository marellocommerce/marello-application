<?php

namespace Marello\Bundle\PaymentBundle;

use Marello\Bundle\PaymentBundle\DependencyInjection\Compiler\CompositePaymentMethodProviderCompilerPass;
use Marello\Bundle\PaymentBundle\DependencyInjection\Compiler\TwigSandboxConfigurationPass;
use Marello\Bundle\PaymentBundle\DependencyInjection\MarelloPaymentExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloPaymentBundle extends Bundle
{
    /** {@inheritdoc} */
    public function getContainerExtension()
    {
        return new MarelloPaymentExtension();
    }

    /** {@inheritdoc} */
    public function build(ContainerBuilder $container)
    {
        //$container->addCompilerPass(new TwigSandboxConfigurationPass());
        $container->addCompilerPass(new CompositePaymentMethodProviderCompilerPass());

        parent::build($container);
    }
}
