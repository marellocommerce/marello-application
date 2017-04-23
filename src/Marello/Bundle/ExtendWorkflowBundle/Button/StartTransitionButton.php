<?php

namespace Marello\Bundle\ExtendWorkflowBundle\Button;

use Oro\Bundle\WorkflowBundle\Button\StartTransitionButton as BaseStartTransitionButton;

class StartTransitionButton extends BaseStartTransitionButton
{
    use TransitionButtonTemplateTrait;
}
