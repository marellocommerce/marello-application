<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Datagrid;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\OrmResultBeforeQuery;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

use MarelloEnterprise\Bundle\InventoryBundle\EventListener\Datagrid\InventoryLevelsGridListener;

class InventoryLevelsGridListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InventoryLevelsGridListener
     */
    protected $inventoryLevelsGridListener;

    protected function setUp()
    {
        $this->inventoryLevelsGridListener = new InventoryLevelsGridListener();
    }

    public function testOnResultBeforeQuery()
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder
            ->expects(static::once())
            ->method('leftJoin')
            ->with('il.warehouse', 'warehouse');

        /** @var OrmResultBeforeQuery|\PHPUnit_Framework_MockObject_MockObject $event **/
        $event = $this->getMockBuilder(OrmResultBeforeQuery::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects(static::once())
            ->method('getQueryBuilder')
            ->willReturn($queryBuilder);

        $this->inventoryLevelsGridListener->onResultBeforeQuery($event);
    }

    public function testOnBuildBefore()
    {
        $addedColumn = [
            'warehouse' => [
                'label' => 'marello.inventory.inventorylevel.warehouse.label',
                'frontend_type' => 'string'
            ]
        ];

        $config = $this->createMock(DatagridConfiguration::class);
        $config
            ->expects(static::once())
            ->method('offsetGetOr')
            ->with('columns', [])
            ->willReturn([]);
        $config->expects(static::once())
            ->method('offsetSet')
            ->with('columns', $addedColumn);

        /** @var BuildBefore|\PHPUnit_Framework_MockObject_MockObject $event **/
        $event = $this->getMockBuilder(BuildBefore::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects(static::once())
            ->method('getConfig')
            ->willReturn($config);

        $this->inventoryLevelsGridListener->onBuildBefore($event);
    }
}
