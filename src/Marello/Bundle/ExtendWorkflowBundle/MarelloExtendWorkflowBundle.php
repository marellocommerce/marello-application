<?php

namespace Marello\Bundle\ExtendWorkflowBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Marello\Bundle\ExtendWorkflowBundle\DependencyInjection\CompilerPass\TransitionButtonProviderExtensionOverridePass;

class MarelloExtendWorkflowBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TransitionButtonProviderExtensionOverridePass());
        parent::build($container);
    }
}
