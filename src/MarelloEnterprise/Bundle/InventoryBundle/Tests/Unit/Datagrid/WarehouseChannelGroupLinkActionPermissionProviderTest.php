<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Datagrid;

use MarelloEnterprise\Bundle\InventoryBundle\Datagrid\WarehouseChannelGroupLinkActionPermissionProvider;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class WarehouseChannelGroupLinkActionPermissionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WarehouseChannelGroupLinkActionPermissionProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new WarehouseChannelGroupLinkActionPermissionProvider();
    }

    /**
     * @dataProvider permissionDataProvider
     * @param bool $system
     * @param array $result
     */
    public function testGetActionPermissions($system, array $result)
    {
        /** @var ResultRecordInterface|\PHPUnit_Framework_MockObject_MockObject $record **/
        $record = $this->createMock(ResultRecordInterface::class);
        $record
            ->expects(static::once())
            ->method('getValue')
            ->with('system')
            ->willReturn($system);

        static::assertEquals($result, $this->provider->getActionPermissions($record));
    }

    /**
     * @return array
     */
    public function permissionDataProvider()
    {
        return [
            [
                'system' => true,
                'result' => [
                    'update' => false,
                    'view' => true,
                    'delete' => false,
                ]
            ],
            [
                'system' => false,
                'result' => [
                    'update' => true,
                    'view' => true,
                    'delete' => true
                ]
            ]
        ];
    }
}
