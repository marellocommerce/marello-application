<?php

namespace Marello\Bundle\Magento2Bundle\Batch\Step;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Step\ItemStep;

/**
 * This logic provides functionality to skip step execution in case when step name is not equals to specific step name.
 *
 * It allows prevent unneeded step execution when group of steps combined under one connector
 */
class ExclusiveItemStep extends ItemStep
{
    /** @var string */
    public const OPTION_KEY_EXCLUSIVE_STEP_NAME = 'exclusive_step_name';

    /** @var string */
    protected $stepName;

    /**
     * @param string $stepName
     */
    public function setstepName(string $stepName): void
    {
        $this->stepName = $stepName;
    }

    /**
     * {@inheritDoc}
     */
    public function doExecute(StepExecution $stepExecution)
    {
        $exclusiveStepName = $stepExecution
            ->getJobExecution()
            ->getExecutionContext()
            ->get(self::OPTION_KEY_EXCLUSIVE_STEP_NAME);

        if ($this->stepName === $exclusiveStepName) {
            parent::doExecute($stepExecution);
        }
    }
}
