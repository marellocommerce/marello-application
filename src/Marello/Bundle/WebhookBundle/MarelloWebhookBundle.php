<?php

namespace Marello\Bundle\WebhookBundle;

use Marello\Bundle\WebhookBundle\DependencyInjection\Compiler\WebhookEventPass;
use Marello\Bundle\WebhookBundle\DependencyInjection\Compiler\WebhookListenersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloWebhookBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new WebhookEventPass())
            ->addCompilerPass(new WebhookListenersCompilerPass());
    }
}
