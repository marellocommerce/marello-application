<?php

namespace Marello\Bundle\LayoutBundle;

use Marello\Bundle\LayoutBundle\DependencyInjection\Compiler\FormChangesProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloLayoutBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FormChangesProviderPass());
        parent::build($container);
    }
}
