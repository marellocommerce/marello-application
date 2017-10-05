<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseSystemGroupListener;

class WarehouseSystemGroupListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WarehouseSystemGroupListener
     */
    protected $warehouseSystemGroupListener;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->warehouseSystemGroupListener = new WarehouseSystemGroupListener();
    }

    /**
     * @dataProvider prePersistDataProvider
     * @param \PHPUnit_Framework_MockObject_MockObject|null $warehouseGroup
     */
    public function testPrePersist(\PHPUnit_Framework_MockObject_MockObject $warehouseGroup = null)
    {
        $warehouse = new Warehouse();

        $repository = $this->createMock(EntityRepository::class);
        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(['system' => true])
            ->willReturn($warehouseGroup);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(static::once())
            ->method('getRepository')
            ->with(WarehouseGroup::class)
            ->willReturn($repository);

        /** @var LifecycleEventArgs|\PHPUnit_Framework_MockObject_MockObject $args **/
        $args = $this
            ->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $args
            ->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($entityManager);

        $this->warehouseSystemGroupListener->prePersist($warehouse, $args);

        static::assertEquals($warehouseGroup, $warehouse->getGroup());
    }

    /**
     * @return array
     */
    public function prePersistDataProvider()
    {
        return [
            'withSystemGroup' => [
                'group' => $this->createMock(WarehouseGroup::class),
            ],
            'noSystemGroup' => [
                'group' => null,
            ]
        ];
    }
}
