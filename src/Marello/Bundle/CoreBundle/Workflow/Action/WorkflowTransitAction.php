<?php

namespace Marello\Bundle\CoreBundle\Workflow\Action;

use Symfony\Component\PropertyAccess\PropertyPathInterface;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\JobQueueBundle\Entity\Job;

use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Action\ActionInterface;
use Oro\Component\Action\Model\ContextAccessor;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class WorkflowTransitAction extends AbstractAction
{
    const WORKFLOW_TRANSIT_COMMAND      = 'oro:workflow:transit';
    const WORKFLOW_WORKFLOWITEM_OPTION  = '--workflow-item';
    const WORKFLOW_TRANSITION_OPTION    = '--transition';

    /** @var ObjectManager $om */
    protected $om;

    /** @var array */
    protected $options;

    /** @var PropertyPathInterface $workflowItem */
    protected $workflowItem;

    /** @var PropertyPathInterface|bool $transitionName */
    protected $transitionName;

    public function __construct(
        ContextAccessor $contextAccessor,
        ObjectManager $om
    ) {
        parent::__construct($contextAccessor);
        $this->om = $om;
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

        $this->createNewTransactionJob($workflowItem, $transitionName);
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

    /**
     * Create new Job for transitioning a workflow item
     * @param WorkflowItem $workflowItem
     * @param $transitionName
     */
    private function createNewTransactionJob(WorkflowItem $workflowItem, $transitionName)
    {
        if (!$workflowItem->getId()) {
            return;
        }

        $job = new Job(
            self::WORKFLOW_TRANSIT_COMMAND,
            $this->getFormattedArguments($workflowItem->getId(), $transitionName),
            true,
            Job::DEFAULT_QUEUE,
            Job::PRIORITY_HIGH
        );

        $this->om->persist($job);
        $this->om->flush();
    }

    /**
     * Format Job arguments into array
     * @param $itemId
     * @param $transitionName
     * @return array
     */
    private function getFormattedArguments($itemId, $transitionName)
    {
        $workflowItemOption = sprintf('%s=%s',self::WORKFLOW_WORKFLOWITEM_OPTION, $itemId);
        $workflowTransitionOption = sprintf('%s=%s',self::WORKFLOW_TRANSITION_OPTION, $transitionName);

        return [$workflowItemOption, $workflowTransitionOption];
    }
}
