<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Datagrid;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use MarelloEnterprise\Bundle\InventoryBundle\EventListener\Datagrid\WarehouseChannelGroupLinkDatagridListener;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;
use Oro\Component\Testing\Unit\EntityTrait;

class WarehouseChannelGroupLinkDatagridListenerTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var WarehouseChannelGroupLinkDatagridListener
     */
    protected $warehouseChannelGroupLinkDatagridListener;

    protected function setUp()
    {
        $this->warehouseChannelGroupLinkDatagridListener = new WarehouseChannelGroupLinkDatagridListener();
    }

    public function testOnResultAfter()
    {
        /** @var OrmResultAfter|\PHPUnit_Framework_MockObject_MockObject $event **/
        $event = $this->getMockBuilder(OrmResultAfter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $warehouse1 = new Warehouse();
        $warehouse2 = new Warehouse();

        $channel1 = new SalesChannel();
        $channel2 = new SalesChannel();
        $channel3 = new SalesChannel();

        $whGroup1 = $this->getEntity(WarehouseGroup::class, ['warehouses' => [$warehouse1]]);
        $whGroup2 = $this->getEntity(WarehouseGroup::class, []);
        $whGroup3 = $this->getEntity(WarehouseGroup::class, ['warehouses' => [$warehouse2]]);

        $scGroup1 = $this->getEntity(SalesChannelGroup::class, []);
        $scGroup2 = $this->getEntity(SalesChannelGroup::class, ['salesChannels' => [$channel1, $channel2]]);
        $scGroup3 = $this->getEntity(SalesChannelGroup::class, ['salesChannels' => [$channel3]]);

        $validRecord = $this->buildRecord($whGroup3, $scGroup3);

        $event
            ->expects(static::once())
            ->method('getRecords')
            ->willReturn([
                $this->buildRecord($whGroup1, $scGroup1),
                $this->buildRecord($whGroup2, $scGroup2),
                $validRecord
            ]);

        $event
            ->expects(static::once())
            ->method('setRecords')
            ->with([$validRecord]);

        $this->warehouseChannelGroupLinkDatagridListener->onResultAfter($event);
    }

    /**
     * @param $whGroup
     * @param $scGroup
     * @return ResultRecord|\PHPUnit_Framework_MockObject_MockObject
     */
    private function buildRecord($whGroup, $scGroup)
    {
        $record = $this->createMock(ResultRecord::class);
        $record
            ->expects(static::at(0))
            ->method('getValue')
            ->with('warehouseGroup')
            ->willReturn($whGroup);
        $record
            ->expects(static::at(1))
            ->method('getValue')
            ->with('salesChannelGroups')
            ->willReturn([$scGroup]);

        return $record;
    }
}
