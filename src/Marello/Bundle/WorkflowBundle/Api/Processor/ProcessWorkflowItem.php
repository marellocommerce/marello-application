<?php

namespace Marello\Bundle\WorkflowBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Symfony\Contracts\Translation\TranslatorInterface;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Helper\WorkflowTranslationHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

class ProcessWorkflowItem implements ProcessorInterface
{
    /**
     * @var WorkflowManager
     */
    private $workflowManager;

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param WorkflowManager $workflowManager
     * @param DoctrineHelper $doctrineHelper
     * @param TranslatorInterface $translator
     */
    public function __construct(
        WorkflowManager $workflowManager,
        DoctrineHelper $doctrineHelper,
        TranslatorInterface $translator
    ) {
        $this->workflowManager = $workflowManager;
        $this->doctrineHelper = $doctrineHelper;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function process(ContextInterface $context)
    {
        /** @var CustomizeLoadedDataContext $context */

        $data = $context->getResult();
        if (!is_array($data)) {
            return;
        }

        $config = $context->getConfig();

        $workflowItemFieldName = $config->findFieldNameByPropertyPath(ProcessWorkflowItemConfig::CONFIG_WORKFLOW_ITEM);

        if (!$workflowItemFieldName
            || $config->getField($workflowItemFieldName)->isExcluded()
            || array_key_exists($workflowItemFieldName, $data)
        ) {
            // the workflowStep field is undefined, excluded or already added
            return;
        }

        $idFieldName = $config->findFieldNameByPropertyPath('id');
        if (!$idFieldName || empty($data[$idFieldName])) {
            // the file id field is undefined or its value is unknown
            return;
        }

        if (!$entityClass = $context->get('class')) {
            return;
        };

        $workflowItem = $this->getWorkflowItem($data[$idFieldName], $entityClass);
        if (null !== $workflowItem) {
            $currentStep = $workflowItem->getCurrentStep();
            $data[$workflowItemFieldName] = [
                'id' => $workflowItem->getId(),
                'currentStep' => [
                    'name' => $currentStep->getName(),
                    'label' => $this->translator->trans(
                        $currentStep->getLabel(),
                        [],
                        WorkflowTranslationHelper::TRANSLATION_DOMAIN
                    )
                ]
            ];
            $context->setResult($data);
        }
    }

    /**
     * @param int $entityId
     * @param string $entityClass
     * @return WorkflowItem|null
     */
    protected function getWorkflowItem($entityId, $entityClass)
    {
        $entity = $this->doctrineHelper
            ->getEntityManagerForClass($entityClass)
            ->getRepository($entityClass)
            ->find($entityId);
        if (!$entity) {
            return null;
        }

        if (!$workflows = $this->workflowManager->getApplicableWorkflows($entity)) {
            return null;
        }
        $workflowNames = array_keys($workflows);
        
        return $this->workflowManager->getWorkflowItem($entity, reset($workflowNames));
    }
}
