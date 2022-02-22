<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseGroupRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use MarelloEnterprise\Bundle\InventoryBundle\Checker\IsFixedWarehouseGroupChecker;
use MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupRemoveListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

class WarehouseGroupREmoveListenerTest extends TestCase
{
    /**
     * @var WarehouseGroupRemoveListener
     */
    private $warehouseGroupRemoveListener;

    /**
     * @var IsFixedWarehouseGroupChecker|\PHPUnit\Framework\MockObject\MockObject
     */
    private $checker;

    /**
     * @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $translator;

    /**
     * @var Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $session;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->session = $this->createMock(Session::class);
        $this->checker = $this->createMock(IsFixedWarehouseGroupChecker::class);
        $this->warehouseGroupRemoveListener = new WarehouseGroupRemoveListener(
            $this->translator,
            $this->session,
            $this->checker
        );
    }

    /**
     * @dataProvider preRemoveDataProvider
     * @param int $qty
     * @param MockObject|null $systemWarehouseGroup
     */
    public function testPreRemove($qty, MockObject $systemWarehouseGroup = null)
    {
        /** @var Warehouse|\PHPUnit\Framework\MockObject\MockObject $warehouse **/
        $warehouse = $this->createMock(Warehouse::class);
        $warehouse
            ->expects(static::exactly($qty))
            ->method('setGroup')
            ->with($systemWarehouseGroup);
        /** @var WarehouseGroup|\PHPUnit\Framework\MockObject\MockObject $warehouseGroup **/
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
        $this->checker->expects($this->once())
            ->method('check')
            ->willReturn(false);
        /** @var LifecycleEventArgs|\PHPUnit\Framework\MockObject\MockObject $args **/
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
