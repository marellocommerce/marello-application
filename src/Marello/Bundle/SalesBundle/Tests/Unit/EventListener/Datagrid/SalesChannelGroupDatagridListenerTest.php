<?php

namespace Marello\Bundle\SalesBundle\Tests\Unit\EventListener\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\EventListener\Datagrid\SalesChannelGroupDatagridListener;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;

class SalesChannelGroupDatagridListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SalesChannelGroupDatagridListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->listener = new SalesChannelGroupDatagridListener();
    }

    public function testOnResultAfter()
    {
        /** @var OrmResultAfter|\PHPUnit_Framework_MockObject_MockObject $event **/
        $event = $this->getMockBuilder(OrmResultAfter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $channel = new SalesChannel();

        $validRecord = $this->buildRecord(new ArrayCollection([$channel]));

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

        $this->listener->onResultAfter($event);
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
            ->with('salesChannels')
            ->willReturn($warehouses);

        return $record;
    }
}
