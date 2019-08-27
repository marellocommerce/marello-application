<?php

namespace Marello\Bundle\CustomerBundle;

use Marello\Bundle\CustomerBundle\DependencyInjection\MarelloCustomerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloCustomerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (!$this->extension) {
            $this->extension = new MarelloCustomerExtension();
        }

        return $this->extension;
    }
}
