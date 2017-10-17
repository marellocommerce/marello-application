<?php

namespace Marello\Bundle\SalesBundle\Tests\Unit\Datagrid;

use Marello\Bundle\SalesBundle\Datagrid\SalesChannelGroupActionPermissionProvider;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class SalesChannelGroupActionPermissionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SalesChannelGroupActionPermissionProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new SalesChannelGroupActionPermissionProvider();
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
