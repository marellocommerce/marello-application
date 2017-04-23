<?php

namespace Marello\Bundle\ExtendWorkflowBundle\Extension;

use Oro\Bundle\ActionBundle\Button\ButtonContext;

use Oro\Bundle\WorkflowBundle\Model\Transition;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Extension\TransitionButtonProviderExtension as BaseTransitionButtonExtension;

use Marello\Bundle\ExtendWorkflowBundle\Button\TransitionButton;

class TransitionButtonProviderExtension extends BaseTransitionButtonExtension
{
    /**
     * {@inheritdoc}
     */
    protected function createTransitionButton(
        Transition $transition,
        Workflow $workflow,
        ButtonContext $buttonContext
    ) {
        return new TransitionButton($transition, $workflow, $buttonContext);
    }
}
