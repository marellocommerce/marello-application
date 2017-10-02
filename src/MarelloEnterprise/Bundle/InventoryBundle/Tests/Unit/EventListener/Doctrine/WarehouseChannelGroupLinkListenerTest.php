<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseChannelGroupLinkListener;

class WarehouseChannelGroupLinkListenerTest extends \PHPUnit_Framework_TestCase
{
    const ENTITY = 'entity';
    const ARGS = 'args';

    /**
     * @var WarehouseChannelGroupLinkListener
     */
    protected $warehouseChannelGroupLinkListener;

    protected function setUp()
    {
        $this->warehouseChannelGroupLinkListener = new WarehouseChannelGroupLinkListener();
    }

    /**
     * @dataProvider prePersistDataProvider
     * @param int $qty
     * @param \PHPUnit_Framework_MockObject_MockObject|null $systemLink
     */
    public function testPrePersist($qty, \PHPUnit_Framework_MockObject_MockObject $systemLink = null)
    {
        $mocks = $this->prepareMocks($qty, $systemLink);

        $this->warehouseChannelGroupLinkListener->prePersist($mocks[self::ENTITY], $mocks[self::ARGS]);
    }

    /**
     * @return array
     */
    public function prePersistDataProvider()
    {
        $systemLink = $this->createMock(WarehouseChannelGroupLink::class);
        $systemLink
            ->expects(static::once())
            ->method('removeSalesChannelGroup');

        return [
            'withDefaultLink' => [
                'qty' => 1,
                'link' => $systemLink
            ],
            'noDefaultLink' => [
                'qty' => 0,
                'link' => null
            ]
        ];
    }

    /**
     * @dataProvider postRemoveDataProvider
     * @param int $qty
     * @param \PHPUnit_Framework_MockObject_MockObject|null $systemLink
     */
    public function testPostRemove($qty, \PHPUnit_Framework_MockObject_MockObject $systemLink = null)
    {
        $mocks = $this->prepareMocks($qty, $systemLink);

        $this->warehouseChannelGroupLinkListener->postRemove($mocks[self::ENTITY], $mocks[self::ARGS]);
    }

    /**
     * @return array
     */
    public function postRemoveDataProvider()
    {
        $systemLink = $this->createMock(WarehouseChannelGroupLink::class);
        $systemLink
            ->expects(static::once())
            ->method('addSalesChannelGroup');

        return [
            'withDefaultLink' => [
                'qty' => 1,
                'link' => $systemLink
            ],
            'noDefaultLink' => [
                'qty' => 0,
                'link' => null
            ]
        ];
    }

    /**
     * @param int $qty
     * @param \PHPUnit_Framework_MockObject_MockObject|null $systemLink
     * @return array
     */
    private function prepareMocks($qty, \PHPUnit_Framework_MockObject_MockObject $systemLink = null)
    {
        /** @var WarehouseChannelGroupLink|\PHPUnit_Framework_MockObject_MockObject $warehouseChannelGroupLink **/
        $warehouseChannelGroupLink = $this->createMock(WarehouseChannelGroupLink::class);
        $warehouseChannelGroupLink
            ->expects(static::exactly($qty))
            ->method('getSalesChannelGroups')
            ->willReturn(
                new ArrayCollection([
                    $this->createMock(SalesChannelGroup::class)
                ])
            );

        /** @var LifecycleEventArgs|\PHPUnit_Framework_MockObject_MockObject $args **/
        $args = $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->createMock(EntityRepository::class);
        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(['system' => true])
            ->willReturn($systemLink);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(static::once())
            ->method('getRepository')
            ->with(WarehouseChannelGroupLink::class)
            ->willReturn($repository);
        $entityManager
            ->expects(static::exactly($qty))
            ->method('persist')
            ->with($systemLink);
        $entityManager
            ->expects(static::exactly($qty))
            ->method('flush');
        $args
            ->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($entityManager);

        return [
            self::ENTITY => $warehouseChannelGroupLink,
            self::ARGS => $args
        ];
    }
}
