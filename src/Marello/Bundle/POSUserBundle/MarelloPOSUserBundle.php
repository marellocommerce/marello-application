<?php

namespace Marello\Bundle\POSUserBundle;

use Marello\Bundle\POSUserBundle\DependencyInjection\CompilerPass\AddAuthenticateProcessorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloPOSUserBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddAuthenticateProcessorCompilerPass());
    }
}
