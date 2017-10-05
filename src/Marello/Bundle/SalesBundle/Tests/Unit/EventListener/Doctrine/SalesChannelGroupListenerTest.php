<?php

namespace Marello\Bundle\SalesBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener;

class SalesChannelGroupListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SalesChannelGroupListener
     */
    protected $salesChannelGroupListener;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->salesChannelGroupListener = new SalesChannelGroupListener();
    }

    /**
     * @dataProvider preRemoveDataProvider
     * @param int $qty
     * @param \PHPUnit_Framework_MockObject_MockObject|null $systemSalesChannelGroup
     * @param \PHPUnit_Framework_MockObject_MockObject|null $systemLink
     */
    public function testPreRemove(
        $qty,
        \PHPUnit_Framework_MockObject_MockObject $systemSalesChannelGroup = null,
        \PHPUnit_Framework_MockObject_MockObject $systemLink = null
    ) {
        /** @var SalesChannel|\PHPUnit_Framework_MockObject_MockObject $salesChannel **/
        $salesChannel = $this->createMock(SalesChannel::class);
        $salesChannel
            ->expects(static::exactly($qty))
            ->method('setGroup')
            ->with($systemSalesChannelGroup);
        /** @var SalesChannelGroup|\PHPUnit_Framework_MockObject_MockObject $salesChannelGroup **/
        $salesChannelGroup = $this->createMock(SalesChannelGroup::class);
        $salesChannelGroup
            ->expects(static::exactly($qty))
            ->method('getSalesChannels')
            ->willReturn([$salesChannel]);

        $repository = $this->createMock(EntityRepository::class);
        $repository
            ->expects(static::at(0))
            ->method('findOneBy')
            ->with(['system' => true])
            ->willReturn($systemSalesChannelGroup);
        $repository
            ->expects(static::at(1))
            ->method('findOneBy')
            ->with(['system' => true])
            ->willReturn($systemLink);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(static::exactly(2))
            ->method('getRepository')
            ->willReturn($repository);
        $entityManager
            ->expects(static::exactly($qty))
            ->method('persist')
            ->with($salesChannel);
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

        $this->salesChannelGroupListener->preRemove($salesChannelGroup, $args);
    }
    
    /**
     * @return array
     */
    public function preRemoveDataProvider()
    {
        return [
            'withSystemGroupWithSystemLink' => [
                'qty' => 1,
                'group' => $this->createMock(SalesChannelGroup::class),
                'link' => $this->createMock(WarehouseChannelGroupLink::class)
            ],
            'noSystemGroupWithSystemLink' => [
                'qty' => 0,
                'group' => null,
                'link' => $this->createMock(WarehouseChannelGroupLink::class)
            ],
            'withSystemGroupNoSystemLink' => [
                'qty' => 0,
                'group' => $this->createMock(SalesChannelGroup::class),
                'link' => null
            ],
            'noSystemGroupNoSystemLink' => [
                'qty' => 0,
                'group' => null,
                'link' => null
            ]
        ];
    }

    /**
     * @dataProvider postPersistDataProvider
     * @param int $qty
     * @param \PHPUnit_Framework_MockObject_MockObject|null $defaultLink
     */
    public function testPostPersist($qty, \PHPUnit_Framework_MockObject_MockObject $defaultLink = null)
    {
        /** @var SalesChannelGroup|\PHPUnit_Framework_MockObject_MockObject $salesChannelGroup **/
        $salesChannelGroup = $this->createMock(SalesChannelGroup::class);

        $repository = $this->createMock(EntityRepository::class);
        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(['system' => true])
            ->willReturn($defaultLink);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(static::once())
            ->method('getRepository')
            ->with(WarehouseChannelGroupLink::class)
            ->willReturn($repository);
        $entityManager
            ->expects(static::exactly($qty))
            ->method('persist')
            ->with($defaultLink);
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

        $this->salesChannelGroupListener->postPersist($salesChannelGroup, $args);
    }

    /**
     * @return array
     */
    public function postPersistDataProvider()
    {
        return [
            'withDefaultLink' => [
                'qty' => 1,
                'link' => $this->createMock(WarehouseChannelGroupLink::class)
            ],
            'noDefaultLink' => [
                'qty' => 0,
                'link' => null
            ]
        ];
    }
}
