<?php

namespace Marello\Bundle\PaymentTermBundle\Datagrid;

use Marello\Bundle\DataGridBundle\Action\ActionPermissionInterface;
use Marello\Bundle\PaymentTermBundle\Provider\PaymentTermDeletePermissionProvider;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class PaymentTermActionPermissionProvider implements ActionPermissionInterface
{
    /**
     * @var PaymentTermDeletePermissionProvider
     */
    protected $paymentTermDeletePermissionProvider;

    /**
     * PaymentTermActionPermissionProvider constructor.
     * @param PaymentTermDeletePermissionProvider $paymentTermDeletePermissionProvider
     */
    public function __construct(PaymentTermDeletePermissionProvider $paymentTermDeletePermissionProvider)
    {
        $this->paymentTermDeletePermissionProvider = $paymentTermDeletePermissionProvider;
    }

    /**
     * @param ResultRecordInterface $record
     * @return array
     */
    public function getActionPermissions(ResultRecordInterface $record)
    {
        return [
            'update' => true,
            'view' => true,
            'delete' => $this->isDeleteAllowed($record),
        ];
    }

    /**
     * @param ResultRecordInterface $record
     * @return bool
     */
    protected function isDeleteAllowed(ResultRecordInterface $record)
    {
        return $this->paymentTermDeletePermissionProvider->isDeleteAllowed($record->getRootEntity());
    }
}
