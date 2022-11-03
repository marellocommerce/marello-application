<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Datagrid;

use Doctrine\ORM\QueryBuilder;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Event\OrmResultBeforeQuery;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

use MarelloEnterprise\Bundle\InventoryBundle\EventListener\Datagrid\InventoryLevelLogGridListener;

class InventoryLevelLogGridListenerTest extends TestCase
{
    /**
     * @var InventoryLevelLogGridListener
     */
    protected $inventoryLevelLogGridListener;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->inventoryLevelLogGridListener = new InventoryLevelLogGridListener();
    }

    /**
     * {@inheritdoc}
     */
    public function testOnResultBeforeQuery()
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder
            ->expects(static::once())
            ->method('addSelect')
            ->with('lr.warehouseName as warehouseLabel')
            ->willReturnSelf();

        /** @var OrmResultBeforeQuery|\PHPUnit\Framework\MockObject\MockObject $event **/
        $event = $this->getMockBuilder(OrmResultBeforeQuery::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects(static::once())
            ->method('getQueryBuilder')
            ->willReturn($queryBuilder);

        $this->inventoryLevelLogGridListener->onResultBeforeQuery($event);
    }

    /**
     * {@inheritdoc}
     */
    public function testOnBuildBefore()
    {
        $columns = ['batchNumber' => []];
        $addedColumn = [
            'warehouseLabel' => [
                'label' => 'marello.inventory.inventorylevel.warehouse.label',
                'frontend_type' => 'string'
            ]
        ];

        $config = $this->createMock(DatagridConfiguration::class);
        $config
            ->expects(static::once())
            ->method('offsetGetOr')
            ->with('columns', [])
            ->willReturn($columns);
        $config->expects(static::once())
            ->method('offsetSet')
            ->with('columns', array_merge($columns, $addedColumn));

        /** @var BuildBefore|\PHPUnit\Framework\MockObject\MockObject $event **/
        $event = $this->getMockBuilder(BuildBefore::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects(static::once())
            ->method('getConfig')
            ->willReturn($config);

        $this->inventoryLevelLogGridListener->onBuildBefore($event);
    }

    /**
     * {@inheritdoc}
     */
    public function testOnBuildBeforeNoColumnAdded()
    {
        $columns = [];
        $config = $this->createMock(DatagridConfiguration::class);
        $config
            ->expects(static::once())
            ->method('offsetGetOr')
            ->with('columns', [])
            ->willReturn($columns);
        $config->expects(static::never())
            ->method('offsetSet');

        /** @var BuildBefore|\PHPUnit\Framework\MockObject\MockObject $event **/
        $event = $this->getMockBuilder(BuildBefore::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects(static::once())
            ->method('getConfig')
            ->willReturn($config);

        $this->inventoryLevelLogGridListener->onBuildBefore($event);
    }
}
