<?php

namespace Marello\Bundle\ReturnBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Marello\Bundle\ReturnBundle\DependencyInjection\CompilerPass\ReturnBusinessRuleRegistryCompilerPass;

class MarelloReturnBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ReturnBusinessRuleRegistryCompilerPass());
    }
}
