<?php

namespace Marello\Bundle\OrderBundle\Datagrid;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\DataGridBundle\Action\ActionPermissionInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class OrderActionPermissionProvider implements ActionPermissionInterface
{
    protected $excludedRefundableSteps = [
        'payment_reminder',
        'pending',
        'cancelled'
    ];

    protected $allowedReturnSteps = [
        'credit',
        'shipped',
        'complete'
    ];

    /** @var ObjectManager */
    protected $objectManager;

    /**
     * @param ObjectManager      $objectManager
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
            'return'    => $this->isReturnApplicable($record),
            'refund'    => $this->isRefundApplicable($record),
            'view'      => true,
            'delete'    => false
        );
    }

    /**
     * {@inheritdoc}
     * @param ResultRecordInterface $record
     * @return bool
     */
    protected function isRefundApplicable($record)
    {
        if (!$this->hasWorkflowStep($record)) {
            return false;
        }

        $workflowStep = $this->getWorkflowStep($record)['stepName'];

        foreach ($this->excludedRefundableSteps as $excludedRefundableStep) {
            if (strpos($workflowStep, $excludedRefundableStep) !== false) {
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     * @param $record
     * @return bool
     */
    protected function isReturnApplicable($record)
    {
        if (!$this->hasWorkflowStep($record)) {
            return false;
        }

        $workflowStep = $this->getWorkflowStep($record)['stepName'];

        foreach ($this->allowedReturnSteps as $allowedReturnStep) {
            if (strpos($workflowStep, $allowedReturnStep) !== false) {
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
