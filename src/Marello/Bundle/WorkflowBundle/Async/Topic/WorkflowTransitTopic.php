<?php

namespace Marello\Bundle\WorkflowBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkflowTransitTopic extends AbstractTopic
{
    public static function getName(): string
    {
        return 'marello_workflow.transit_workflow';
    }

    public static function getDescription(): string
    {
        return 'Workflow transit';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'workflow_item_entity_id',
                'current_step_id',
                'entity_class',
                'transition',
                'priority',
                'jobId',
            ])
            ->setRequired([
                'workflow_item_entity_id',
                'current_step_id',
                'entity_class',
                'transition',
                'priority',
            ])
            ->addAllowedTypes('workflow_item_entity_id', ['int'])
            ->addAllowedTypes('current_step_id', ['int'])
            ->addAllowedTypes('entity_class', ['string'])
            ->addAllowedTypes('transition', ['string'])
            ->addAllowedTypes('priority', ['string'])
            ->addAllowedTypes('jobId', ['int']);
    }
}
