<?php

namespace Marello\Bundle\FilterBundle;

use Marello\Bundle\FilterBundle\DependencyInjection\CompilerPass\FilterCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloFilterBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FilterCompilerPass());
        parent::build($container);
    }
}
