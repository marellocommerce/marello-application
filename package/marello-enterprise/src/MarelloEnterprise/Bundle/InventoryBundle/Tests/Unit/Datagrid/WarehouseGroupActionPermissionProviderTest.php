<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Datagrid;

use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use MarelloEnterprise\Bundle\InventoryBundle\Checker\IsFixedWarehouseGroupChecker;
use MarelloEnterprise\Bundle\InventoryBundle\Datagrid\WarehouseGroupActionPermissionProvider;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class WarehouseGroupActionPermissionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IsFixedWarehouseGroupChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checker;

    /**
     * @var WarehouseGroupActionPermissionProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->checker = $this->createMock(IsFixedWarehouseGroupChecker::class);
        $this->provider = new WarehouseGroupActionPermissionProvider($this->checker);
    }

    /**
     * @dataProvider permissionDataProvider
     * @param bool $system
     * @param bool $fixed
     * @param array $result
     */
    public function testGetActionPermissions($system, $fixed, array $result)
    {
        /** @var ResultRecordInterface|\PHPUnit_Framework_MockObject_MockObject $record **/
        $record = $this->createMock(ResultRecordInterface::class);
        $record
            ->expects(static::once())
            ->method('getValue')
            ->with('system')
            ->willReturn($system);

        $group = $this->createMock(WarehouseGroup::class);
        $record
            ->expects(static::once())
            ->method('getRootEntity')
            ->willReturn($group);

        $this->checker
            ->expects(static::any())
            ->method('check')
            ->with($group)
            ->willReturn($fixed);

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
                'fixed' => true,
                'result' => [
                    'update' => false,
                    'view' => true,
                    'delete' => false,
                ]
            ],
            [
                'system' => false,
                'fixed' => true,
                'result' => [
                    'update' => true,
                    'view' => true,
                    'delete' => false
                ]
            ],
            [
                'system' => false,
                'fixed' => false,
                'result' => [
                    'update' => true,
                    'view' => true,
                    'delete' => true
                ]
            ],
            [
                'system' => true,
                'fixed' => false,
                'result' => [
                    'update' => false,
                    'view' => true,
                    'delete' => false
                ]
            ]
        ];
    }
}
