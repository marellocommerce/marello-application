<?php

namespace Marello\Bundle\WorkflowBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\TranslationBundle\Translation\Translator;
use Oro\Bundle\WorkflowBundle\Helper\WorkflowTranslationHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

class ProcessWorkflowStep implements ProcessorInterface
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
     * @var Translator
     */
    private $translator;

    /**
     * @param WorkflowManager $workflowManager
     * @param DoctrineHelper $doctrineHelper
     * @param Translator $translator
     */
    public function __construct(
        WorkflowManager $workflowManager,
        DoctrineHelper $doctrineHelper,
        Translator $translator
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

        $workflowStepFieldName = $config->findFieldNameByPropertyPath(ProcessWorkflowStepConfig::CONFIG_WORKFLOW_STEP);
        if (!$workflowStepFieldName
            || $config->getField($workflowStepFieldName)->isExcluded()
            || array_key_exists($workflowStepFieldName, $data)
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

        $workflowStep = $this->getWorkflowStep($data[$idFieldName], $entityClass);
        if (null !== $workflowStep) {
            $data[$workflowStepFieldName] = $workflowStep;
            $context->setResult($data);
        }
    }

    /**
     * @param int $entityId
     * @param string $entityClass
     * @return string|null
     */
    protected function getWorkflowStep($entityId, $entityClass)
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
        $workflowItem = $this->workflowManager->getWorkflowItem($entity, reset($workflowNames));

        if (!$workflowItem) {
            return null;
        }

        if ($currentStep = $workflowItem->getCurrentStep()) {
            return $this->translator->trans(
                $currentStep->getLabel(),
                [],
                WorkflowTranslationHelper::TRANSLATION_DOMAIN
            );
        }
        return null;
    }
}
