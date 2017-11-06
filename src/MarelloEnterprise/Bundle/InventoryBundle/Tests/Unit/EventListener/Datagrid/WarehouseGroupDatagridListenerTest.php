<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use MarelloEnterprise\Bundle\InventoryBundle\EventListener\Datagrid\WarehouseGroupDatagridListener;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;

class WarehouseGroupDatagridListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WarehouseGroupDatagridListener
     */
    protected $warehouseGroupDatagridListener;

    protected function setUp()
    {
        $this->warehouseGroupDatagridListener = new WarehouseGroupDatagridListener();
    }

    public function testOnResultAfter()
    {
        /** @var OrmResultAfter|\PHPUnit_Framework_MockObject_MockObject $event **/
        $event = $this->getMockBuilder(OrmResultAfter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $warehouse = new Warehouse();

        $validRecord = $this->buildRecord(new ArrayCollection([$warehouse]));

        $event
            ->expects(static::once())
            ->method('getRecords')
            ->willReturn([
                $this->buildRecord(new ArrayCollection([])),
                $validRecord
            ]);

        $event
            ->expects(static::once())
            ->method('setRecords')
            ->with([$validRecord]);

        $this->warehouseGroupDatagridListener->onResultAfter($event);
    }

    /**
     * @param ArrayCollection $warehouses
     * @return ResultRecord|\PHPUnit_Framework_MockObject_MockObject
     */
    private function buildRecord($warehouses)
    {
        $record = $this->createMock(ResultRecord::class);
        $record
            ->expects(static::once())
            ->method('getValue')
            ->with('warehouses')
            ->willReturn($warehouses);

        return $record;
    }
}
