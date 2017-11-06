<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Datagrid;

use Marello\Bundle\DataGridBundle\Action\ActionPermissionInterface;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use MarelloEnterprise\Bundle\InventoryBundle\Checker\IsFixedWarehouseGroupChecker;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class WarehouseGroupActionPermissionProvider implements ActionPermissionInterface
{
    /**
     * @var IsFixedWarehouseGroupChecker
     */
    protected $checker;

    /**
     * @param IsFixedWarehouseGroupChecker $checker
     */
    public function __construct(IsFixedWarehouseGroupChecker $checker)
    {
        $this->checker = $checker;
    }
 
    /**
     * {@inheritdoc}
     */
    public function getActionPermissions(ResultRecordInterface $record)
    {
        $delete = true;
        /** @var WarehouseGroup $group */
        $group = $record->getRootEntity();
        $system = $record->getValue('system');
        if ($system || $this->checker->check($group)) {
            $delete = false;
        }
        
        return [
            'update' => !$system,
            'view' => true,
            'delete' => $delete,
        ];
    }
}
