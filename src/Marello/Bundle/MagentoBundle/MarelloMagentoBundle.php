<?php

namespace Marello\Bundle\MagentoBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Marello\Bundle\MagentoBundle\Async\Topics;
use Oro\Bundle\MessageQueueBundle\DependencyInjection\Compiler\AddTopicMetaPass;
use Marello\Bundle\MagentoBundle\DependencyInjection\Compiler\ResponseConvertersPass;

class MarelloMagentoBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $addTopicPass = AddTopicMetaPass::create()
            ->add(Topics::SYNC_INITIAL_INTEGRATION)
        ;
        $container->addCompilerPass($addTopicPass);
        $container->addCompilerPass(new ResponseConvertersPass());
    }
}
