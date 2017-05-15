<?php

namespace Marello\Bundle\OrderBundle\Datagrid;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

use Marello\Bundle\DataGridBundle\Action\ActionPermissionInterface;

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

        $workflowStep = $this->getWorkflowStep($record);

        return (!in_array($workflowStep->getName(), $this->excludedRefundableSteps));
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

        $workflowStep = $this->getWorkflowStep($record);

        return (in_array($workflowStep->getName(), $this->allowedReturnSteps));
    }

    /**
     * check if record has a workflow step
     * @param ResultRecordInterface $record
     * @return bool
     */
    protected function hasWorkflowStep(ResultRecordInterface $record)
    {
        return (bool) ($record->getValue('workflowStep'));
    }

    /**
     * Get workflowstep
     * @param ResultRecordInterface $record
     * @return mixed
     */
    protected function getWorkflowStep(ResultRecordInterface $record)
    {
        return $record->getValue('workflowStep');
    }
}
