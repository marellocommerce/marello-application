<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Datagrid;

use MarelloEnterprise\Bundle\InventoryBundle\Datagrid\WarehouseActionPermissionProvider;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class WarehouseActionPermissionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WarehouseActionPermissionProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new WarehouseActionPermissionProvider();
    }

    /**
     * @dataProvider permissionDataProvider
     * @param bool $default
     * @param array $result
     */
    public function testGetActionPermissions($default, array $result)
    {
        /** @var ResultRecordInterface|\PHPUnit_Framework_MockObject_MockObject $record **/
        $record = $this->createMock(ResultRecordInterface::class);
        $record
            ->expects(static::once())
            ->method('getValue')
            ->with('default')
            ->willReturn($default);
        
        static::assertEquals($result, $this->provider->getActionPermissions($record));
    }

    /**
     * @return array
     */
    public function permissionDataProvider()
    {
        return [
            [
                'default' => true,
                'result' => [
                    'update' => true,
                    'view' => true,
                    'delete' => false,
                ]
            ],
            [
                'default' => false,
                'result' => [
                    'update' => true,
                    'view' => true,
                    'delete' => true
                ]
            ]
        ];
    }
}
