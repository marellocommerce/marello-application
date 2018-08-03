<?php

namespace Marello\Bundle\UPSBundle;

use Marello\Bundle\UPSBundle\DependencyInjection\CompilerPass\UPSRequestFactoriesCompilerPass;
use Marello\Bundle\UPSBundle\DependencyInjection\MarelloUPSExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloUPSBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new UPSRequestFactoriesCompilerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (!$this->extension) {
            $this->extension = new MarelloUPSExtension();
        }

        return $this->extension;
    }
}
