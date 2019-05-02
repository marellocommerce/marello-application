<?php

namespace Marello\Bundle\NotificationBundle;

use Marello\Bundle\NotificationBundle\DependencyInjection\Compiler\EntityNotificationConfigurationProvidersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloNotificationBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EntityNotificationConfigurationProvidersPass());
        parent::build($container);
    }
}
