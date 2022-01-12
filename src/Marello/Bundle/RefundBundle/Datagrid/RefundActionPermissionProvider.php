<?php

namespace Marello\Bundle\RefundBundle\Datagrid;

use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\DataGridBundle\Action\ActionPermissionInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class RefundActionPermissionProvider implements ActionPermissionInterface
{
    protected $allowedUpdateSteps = [
        'pending'
    ];

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param ResultRecordInterface $record
     * @return array
     */
    public function getActionPermissions(ResultRecordInterface $record)
    {
        return array(
            'update'    => $this->isUpdateApplicable($record),
            'view'      => true,
            'delete'    => false
        );
    }

    /**
     * {@inheritdoc}
     * @param $record
     * @return bool
     */
    protected function isUpdateApplicable($record)
    {
        if (!$this->hasWorkflowStep($record)) {
            return false;
        }

        $workflowStep = $this->getWorkflowStep($record)['stepName'];

        foreach ($this->allowedUpdateSteps as $allowedUpdateStep) {
            if (strpos($workflowStep, $allowedUpdateStep) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * check if record has a workflow step
     * @param ResultRecordInterface $record
     * @return bool
     */
    protected function hasWorkflowStep(ResultRecordInterface $record)
    {
        return (bool) ($record->getValue('workflowStepLabel'));
    }

    /**
     * Get workflowstep
     * @param ResultRecordInterface $record
     * @return mixed
     */
    protected function getWorkflowStep(ResultRecordInterface $record)
    {
        $value = $record->getValue('workflowStepLabel');

        return reset($value);
    }
}
