<?php

namespace Marello\Bundle\ExtendWorkflowBundle\Api\Processor\GetList;

use Oro\Bundle\ApiBundle\Processor\Context;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

class LoadWorkflowItemsForEntities implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     * @param ContextInterface $context
     */
    public function process(ContextInterface $context)
    {
        /** @var Context $context */

        if (!$context->hasResult()) {
            // data is not retrieved yet
            return;
        }
    }
}
