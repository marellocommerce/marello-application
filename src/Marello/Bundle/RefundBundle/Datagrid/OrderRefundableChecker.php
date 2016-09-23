<?php

namespace Marello\Bundle\RefundBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

class OrderRefundableChecker
{
    protected function isRefundable(WorkflowStep $step)
    {
        return ($step->getName() === 'credit') || ($step->getName() === 'invoice');
    }

    public function getRefundableCallback()
    {
        return function (ResultRecordInterface $record) {
            return [
                'view'   => true,
                'return' => true,
                'refund' => $this->isRefundable($record->getValue('workflowStep')),
            ];
        };
    }
}
