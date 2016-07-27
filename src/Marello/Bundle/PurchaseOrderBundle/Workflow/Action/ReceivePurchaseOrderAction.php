<?php

namespace Marello\Bundle\PurchaseOrderBundle\Workflow\Action;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Model\Action\AbstractAction;
use Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface;

class ReceivePurchaseOrderAction extends AbstractAction
{

    /**
     * @param mixed $context
     */
    protected function executeAction($context)
    {
        // TODO: Implement executeAction() method.
    }

    /**
     * Initialize action based on passed options.
     *
     * @param array $options
     * @return ActionInterface
     * @throws InvalidParameterException
     */
    public function initialize(array $options)
    {
        // TODO: Implement initialize() method.
    }
}
