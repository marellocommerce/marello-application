<?php

namespace Marello\Bundle\ExtendWorkflowBundle\Extension;

use Oro\Bundle\ActionBundle\Button\ButtonContext;

use Oro\Bundle\WorkflowBundle\Model\Transition;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Extension\StartTransitionButtonProviderExtension as BaseStartTransitionButtonExtension;

use Marello\Bundle\ExtendWorkflowBundle\Button\StartTransitionButton;

class StartTransitionButtonProviderExtension extends BaseStartTransitionButtonExtension
{
    /**
     * {@inheritdoc}
     */
    protected function createTransitionButton(
        Transition $transition,
        Workflow $workflow,
        ButtonContext $buttonContext
    ) {
        return new StartTransitionButton($transition, $workflow, $buttonContext);
    }
}
