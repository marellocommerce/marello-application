<?php

namespace Marello\Bundle\SalesBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupRemoveListener;

class SalesChannelGroupRemoveListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SalesChannelGroupRemoveListener
     */
    protected $salesChannelGroupRemoveListener;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->salesChannelGroupRemoveListener = new SalesChannelGroupRemoveListener();
    }

    /**
     * @dataProvider preRemoveDataProvider
     * @param int $qty
     * @param \PHPUnit_Framework_MockObject_MockObject|null $systemSalesChannelGroup
     */
    public function testPreRemove($qty, \PHPUnit_Framework_MockObject_MockObject $systemSalesChannelGroup = null)
    {
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
            ->expects(static::once())
            ->method('findOneBy')
            ->with(['system' => true])
            ->willReturn($systemSalesChannelGroup);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(static::once())
            ->method('getRepository')
            ->with(SalesChannelGroup::class)
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

        $this->salesChannelGroupRemoveListener->preRemove($salesChannelGroup, $args);
    }
    
    /**
     * @return array
     */
    public function preRemoveDataProvider()
    {
        return [
            'withSystemGroup' => [
                'qty' => 1,
                'group' => $this->createMock(SalesChannelGroup::class)
            ],
            'noSystemGroup' => [
                'qty' => 0,
                'group' => null
            ]
        ];
    }
}
