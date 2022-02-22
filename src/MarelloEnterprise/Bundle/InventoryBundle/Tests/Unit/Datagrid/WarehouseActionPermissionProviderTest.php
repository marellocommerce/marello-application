<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Datagrid;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

use MarelloEnterprise\Bundle\InventoryBundle\Datagrid\WarehouseActionPermissionProvider;

class WarehouseActionPermissionProviderTest extends TestCase
{
    /**
     * @var WarehouseActionPermissionProvider
     */
    protected $provider;

    protected function setUp(): void
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
        /** @var ResultRecordInterface|\PHPUnit\Framework\MockObject\MockObject $record **/
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
                    'marello_delete_warehouse' => false,
                ]
            ],
            [
                'default' => false,
                'result' => [
                    'update' => true,
                    'view' => true,
                    'marello_delete_warehouse' => true
                ]
            ]
        ];
    }
}
