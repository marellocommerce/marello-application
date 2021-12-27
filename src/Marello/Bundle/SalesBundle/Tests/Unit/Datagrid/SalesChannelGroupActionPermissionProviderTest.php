<?php

namespace Marello\Bundle\SalesBundle\Tests\Unit\Datagrid;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

use Marello\Bundle\SalesBundle\Datagrid\SalesChannelGroupActionPermissionProvider;

class SalesChannelGroupActionPermissionProviderTest extends TestCase
{
    /**
     * @var SalesChannelGroupActionPermissionProvider
     */
    protected $provider;

    protected function setUp(): void
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
        /** @var ResultRecordInterface|\PHPUnit\Framework\MockObject\MockObject $record **/
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
