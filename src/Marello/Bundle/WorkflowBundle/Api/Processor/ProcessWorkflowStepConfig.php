<?php

namespace Marello\Bundle\WorkflowBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Config\EntityDefinitionConfig;
use Oro\Bundle\ApiBundle\Config\EntityDefinitionFieldConfig;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

class ProcessWorkflowStepConfig implements ProcessorInterface
{
    const CONFIG_WORKFLOW_STEP = 'workflowStep';

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var EntityDefinitionConfig $config */
        $config = $context->getResult();
        if (!$config) {
            return;
        }
        if ($config->hasField(self::CONFIG_WORKFLOW_STEP)) {
            return;
        }

        $fieldConfig = new EntityDefinitionFieldConfig();
        $fieldConfig->setDataType('string');
        $fieldConfig->setExcluded(false);
        $fieldConfig->setFormOptions(
            [
                'mapped' => false
            ]
        );

        $config->addField(self::CONFIG_WORKFLOW_STEP, $fieldConfig);
    }
}
