<?php

namespace Marello\Bundle\SalesBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelGroupRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelListener;

class SalesChannelListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SalesChannelListener
     */
    protected $salesChannelListener;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->salesChannelListener = new SalesChannelListener(true);
    }

    /**
     * @dataProvider prePersistDataProvider
     * @param \PHPUnit_Framework_MockObject_MockObject|null $salesChannelGroup
     */
    public function testPrePersist(\PHPUnit_Framework_MockObject_MockObject $salesChannelGroup = null)
    {
        $salesChannel = new SalesChannel();

        $repository = $this->createMock(SalesChannelGroupRepository::class);
        $repository
            ->expects(static::once())
            ->method('findSystemChannelGroup')
            ->willReturn($salesChannelGroup);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(static::once())
            ->method('getRepository')
            ->with(SalesChannelGroup::class)
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

        $this->salesChannelListener->prePersist($salesChannel, $args);

        static::assertEquals($salesChannelGroup, $salesChannel->getGroup());
    }

    /**
     * @return array
     */
    public function prePersistDataProvider()
    {
        return [
            'withSystemGroup' => [
                'group' => $this->createMock(SalesChannelGroup::class),
            ],
            'noSystemGroup' => [
                'group' => null,
            ]
        ];
    }
}
