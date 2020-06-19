<?php

namespace Marello\Bundle\Magento2Bundle\Batch\Step;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Step\ItemStep;

/**
 * This logic provides functionality to use simple routing within step logic,
 * to collect all steps that can be use in scope of one connector, but run only required.
 */
class ActionItemStep extends ItemStep
{
    /** @var string */
    public const OPTION_KEY_ACTION_NAME = 'actionName';

    /** @var string */
    protected $actionName;

    /**
     * @param string $stepName
     */
    public function setActionName(string $stepName): void
    {
        $this->actionName = $stepName;
    }

    /**
     * {@inheritDoc}
     */
    public function doExecute(StepExecution $stepExecution)
    {
        $actionName = $stepExecution
            ->getJobExecution()
            ->getExecutionContext()
            ->get(self::OPTION_KEY_ACTION_NAME);

        if ($this->actionName === $actionName) {
            parent::doExecute($stepExecution);
        }
    }
}
