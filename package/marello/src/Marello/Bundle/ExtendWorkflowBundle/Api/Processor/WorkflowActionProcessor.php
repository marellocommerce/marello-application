<?php

namespace Marello\Bundle\ExtendWorkflowBundle\Api\Processor;

use Marello\Bundle\ExtendWorkflowBundle\Api\Processor\Context\WorkflowContext;
use Oro\Bundle\ApiBundle\Processor\RequestActionProcessor;

class WorkflowActionProcessor extends RequestActionProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function createContextObject()
    {
        return new WorkflowContext($this->configProvider, $this->metadataProvider);
    }
}
