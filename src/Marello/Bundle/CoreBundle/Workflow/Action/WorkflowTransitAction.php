<?php

namespace Marello\Bundle\CoreBundle\Workflow\Action;

use Symfony\Component\PropertyAccess\PropertyPathInterface;

use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Action\ActionInterface;
use Oro\Component\Action\Model\ContextAccessor;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class WorkflowTransitAction extends AbstractAction
{
    /** @var array */
    protected $options;

    /** @var WorkflowManager $workflowManager */
    protected $workflowManager;

    /** @var PropertyPathInterface $workflowItem */
    protected $workflowItem;

    /** @var PropertyPathInterface|bool $transitionName */
    protected $transitionName;

    /**
     * ReceivePurchaseOrderAction constructor.
     * @param ContextAccessor $contextAccessor
     * @param WorkflowManager $workflowManager
     */
    public function __construct(
        ContextAccessor $contextAccessor,
        WorkflowManager $workflowManager
    ) {
        parent::__construct($contextAccessor);

        $this->workflowManager  = $workflowManager;
    }

    /**
     * {@inheritdoc}
     * @param mixed $context
     * @throws \Exception
     */
    protected function executeAction($context)
    {
        /** @var WorkflowItem $workflowItem */
        $workflowItem = $this->contextAccessor->getValue($context, $this->workflowItem);

        if (!$workflowItem instanceof WorkflowItem) {
            return;
        }

        $transitionName = $this->contextAccessor->getValue($context, $this->transitionName);
        if (!$transitionName) {
            throw new \Exception('Invalid configuration of workflow action, expected transactionName, null given');
        }

        $workflow = $this->workflowManager->getWorkflow($workflowItem);
        $transaction = $workflow->getTransitionManager()->getTransition($transitionName);

        $this->workflowManager->transit($workflowItem, $transaction);
    }

    /**
     * Initialize action based on passed options.
     *
     * @param array $options
     *
     * @return ActionInterface
     * @throws InvalidParameterException
     */
    public function initialize(array $options)
    {
        if (!array_key_exists('workflowItem', $options) && !$options['workflowItem'] instanceof PropertyPathInterface) {
            throw new InvalidParameterException('Parameter "workflowItem" is required.');
        } else {
            $this->workflowItem = $this->getOption($options, 'workflowItem');
        }

        if (!array_key_exists('transitionName', $options) && !$options['transitionName'] instanceof PropertyPathInterface) {
            throw new InvalidParameterException('Parameter "transitionName" is required.');
        } else {
            $this->transitionName = $this->getOption($options, 'transitionName');
        }
    }
}
