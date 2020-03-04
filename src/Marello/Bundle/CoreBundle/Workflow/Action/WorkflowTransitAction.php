<?php

namespace Marello\Bundle\CoreBundle\Workflow\Action;

use Symfony\Component\PropertyAccess\PropertyPathInterface;

use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Action\ActionInterface;
use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class WorkflowTransitAction extends AbstractAction
{
    /** @var array */
    protected $options;

    /** @var PropertyPathInterface|bool $transitionName */
    protected $transitionName;

    /** @var  WorkflowManager */
    protected $workflowManager;

    /**
     * WorkflowTransitAction constructor.
     * @param ContextAccessor $contextAccessor
     * @param WorkflowManager $workflowManager
     */
    public function __construct(
        ContextAccessor $contextAccessor,
        WorkflowManager $workflowManager
    ) {
        parent::__construct($contextAccessor);
        $this->workflowManager = $workflowManager;
    }

    /**
     * {@inheritdoc}
     * @param mixed $context
     * @throws \Exception
     */
    protected function executeAction($context)
    {
        /** @var WorkflowItem $workflowItem */
        $workflowItem = $context;

        if (!$workflowItem) {
            throw new \Exception('Invalid configuration of workflow action, expected workflowItem, none given.');
        }

        if (!$workflowItem instanceof WorkflowItem) {
            return;
        }

        $transitionName = $this->contextAccessor->getValue($context, $this->transitionName);
        if (!$transitionName) {
            throw new \Exception('Invalid configuration of workflow action, expected transitionName, none given.');
        }

        $this->workflowManager->transit($workflowItem, $transitionName);
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
        if (!array_key_exists('transitionName', $options)) {
            throw new InvalidParameterException('Parameter "transitionName" is required.');
        } else {
            $this->transitionName = $this->getOption($options, 'transitionName');
        }

        $this->options = $options;

        return $this;
    }
}
