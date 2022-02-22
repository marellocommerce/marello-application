<?php

namespace Marello\Bundle\RefundBundle;

use Marello\Bundle\RefundBundle\DependencyInjection\Compiler\RefundItemDataProvidersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloRefundBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new RefundItemDataProvidersPass());
        parent::build($container);
    }
}
