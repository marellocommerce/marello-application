<?php

namespace Marello\Bundle\OrderBundle\Datagrid;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;

class ActionPermissionProvider
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
    public function getOrderActionPermissions(ResultRecordInterface $record)
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
        $workflowStep = $record->getValue('workflowStep');
        if (!$workflowStep) {
            return false;
        }

        return (!in_array($workflowStep->getName(), $this->excludedRefundableSteps));
    }

    /**
     * {@inheritdoc}
     * @param $record
     * @return bool
     */
    protected function isReturnApplicable($record)
    {
        // workflow step allowed
        $workflowStep = $record->getValue('workflowStep');
        if (!$workflowStep) {
            return false;
        }

        return (in_array($workflowStep->getName(), $this->allowedReturnSteps));
    }
}
