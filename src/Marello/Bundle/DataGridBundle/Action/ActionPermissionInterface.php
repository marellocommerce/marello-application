<?php

namespace Marello\Bundle\DataGridBundle\Action;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

interface ActionPermissionInterface
{
    /**
     * Get action permissions for data grid row
     * @param ResultRecordInterface $record
     * @return mixed
     */
    public function getActionPermissions(ResultRecordInterface $record);
}
