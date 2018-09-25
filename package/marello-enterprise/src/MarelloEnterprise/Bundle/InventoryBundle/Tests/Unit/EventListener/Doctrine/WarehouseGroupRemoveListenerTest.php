<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseGroupRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupRemoveListener;

class WarehouseGroupREmoveListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WarehouseGroupRemoveListener
     */
    protected $warehouseGroupRemoveListener;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->warehouseGroupRemoveListener = new WarehouseGroupRemoveListener();
    }

    /**
     * @dataProvider preRemoveDataProvider
     * @param int $qty
     * @param \PHPUnit_Framework_MockObject_MockObject|null $systemWarehouseGroup
     */
    public function testPreRemove($qty, \PHPUnit_Framework_MockObject_MockObject $systemWarehouseGroup = null)
    {
        /** @var Warehouse|\PHPUnit_Framework_MockObject_MockObject $warehouse **/
        $warehouse = $this->createMock(Warehouse::class);
        $warehouse
            ->expects(static::exactly($qty))
            ->method('setGroup')
            ->with($systemWarehouseGroup);
        /** @var WarehouseGroup|\PHPUnit_Framework_MockObject_MockObject $warehouseGroup **/
        $warehouseGroup = $this->createMock(WarehouseGroup::class);
        $warehouseGroup
            ->expects(static::exactly($qty))
            ->method('getWarehouses')
            ->willReturn([$warehouse]);

        $repository = $this->createMock(WarehouseGroupRepository::class);
        $repository
            ->expects(static::once())
            ->method('findSystemWarehouseGroup')
            ->willReturn($systemWarehouseGroup);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(static::once())
            ->method('getRepository')
            ->with(WarehouseGroup::class)
            ->willReturn($repository);
        $entityManager
            ->expects(static::exactly($qty))
            ->method('persist')
            ->with($warehouse);
        $entityManager
            ->expects(static::exactly($qty))
            ->method('flush');

        /** @var LifecycleEventArgs|\PHPUnit_Framework_MockObject_MockObject $args **/
        $args = $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $args
            ->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($entityManager);

        $this->warehouseGroupRemoveListener->preRemove($warehouseGroup, $args);
    }
    
    /**
     * @return array
     */
    public function preRemoveDataProvider()
    {
        return [
            'withSystemGroup' => [
                'qty' => 1,
                'group' => $this->createMock(WarehouseGroup::class)
            ],
            'noSystemGroup' => [
                'qty' => 0,
                'group' => null
            ]
        ];
    }
}
