<?php

namespace Marello\Bundle\ExtendWorkflowBundle\Api\Processor\Get;

use Oro\Bundle\ApiBundle\Processor\Context;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\EntitySerializer\EntityConfig;
use Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowItemRepository;

class LoadWorkflowItemsForEntity implements ProcessorInterface
{
    const WORKFLOW_ITEM_FIELD   = 'workflowItems';
    const WORKFLOW_ITEM_FQCN    = 'Oro\Bundle\WorkflowBundle\Entity\WorkflowItem';

    /** @var WorkflowManager $workflowManager */
    protected $workflowManager;

    /** @var WorkflowItemRepository $workflowItemRepository */
    protected $workflowItemRepository;

    public function __construct(
        WorkflowManager $workflowManager,
        WorkflowItemRepository $workflowItemRepository
    ) {
        $this->workflowManager = $workflowManager;
        $this->workflowItemRepository = $workflowItemRepository;
    }

    /**
     * {@inheritdoc}
     * @param ContextInterface $context
     */
    public function process(ContextInterface $context)
    {
        /** @var Context $context */
        $items = $this->workflowItemRepository->findByEntityMetadata($context->getClassName(), 1);
        foreach ($items as $item) {
            file_put_contents(
                '/var/www/app/logs/result_api.log',
                __METHOD__ . " " . print_r($item->getCurrentStep()->getName(), true) . "\r\n",
                FILE_APPEND
            );
        }

        if (!$context->hasResult()) {
            // data is not retrieved yet
            return;
        }

        $config = $context->getConfig();


        if (null === $config) {
            // an entity configuration does not exist
            return;
        }

        if (!$this->hasWorkflowItemField($config) ||
            !$this->hasWorkflowAssociation($context->getResult())) {
            return;
        }
    }


    /**
     * Check if the field exists in the Entity config
     * @param EntityConfig $config
     * @return bool
     */
    protected function hasWorkflowItemField(EntityConfig $config)
    {
        return $config->hasField(self::WORKFLOW_ITEM_FIELD);
    }

    /**
     * Check if there are in fact workflows on the given entity
     * @param $entity
     * @return bool
     */
    protected function hasWorkflowAssociation($entity)
    {
        return $this->workflowManager->hasWorkflowItemsByEntity($entity);
    }
}
