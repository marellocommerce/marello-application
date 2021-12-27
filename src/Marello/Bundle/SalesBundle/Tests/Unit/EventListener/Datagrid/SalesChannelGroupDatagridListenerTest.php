<?php

namespace Marello\Bundle\SalesBundle\Tests\Unit\EventListener\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\EventListener\Datagrid\SalesChannelGroupDatagridListener;

class SalesChannelGroupDatagridListenerTest extends TestCase
{
    /**
     * @var SalesChannelGroupDatagridListener
     */
    protected $listener;

    protected function setUp(): void
    {
        $this->listener = new SalesChannelGroupDatagridListener();
    }

    public function testOnResultAfter()
    {
        /** @var OrmResultAfter|\PHPUnit\Framework\MockObject\MockObject $event **/
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
     * @return ResultRecord|\PHPUnit\Framework\MockObject\MockObject
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
