<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\DependencyInjection\CompilerPass\AddOroProcessorsCompilerPass;

class MarelloEnterpriseInstoreAssistantBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddOroProcessorsCompilerPass());
    }
}
