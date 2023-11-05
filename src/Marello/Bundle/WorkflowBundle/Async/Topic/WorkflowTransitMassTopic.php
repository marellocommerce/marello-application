<?php

namespace Marello\Bundle\WorkflowBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkflowTransitMassTopic extends AbstractTopic
{
    public static function getName(): string
    {
        return 'marello_workflow.workflow_transit_mass';
    }

    public static function getDescription(): string
    {
        return 'Workflow mass transit';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'datagridName',
                'actionName',
                'parameters',
                'userEmail',
                'batchSize',
            ])
            ->setRequired([
                'datagridName',
                'actionName',
                'parameters',
                'userEmail',
                'batchSize',
            ])
            ->addAllowedTypes('datagridName', ['string'])
            ->addAllowedTypes('actionName', ['string'])
            ->addAllowedTypes('parameters', ['array'])
            ->addAllowedTypes('userEmail', ['string'])
            ->addAllowedTypes('batchSize', ['int']);
    }
}
