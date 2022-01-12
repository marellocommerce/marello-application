<?php

namespace Marello\Bundle\SalesBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\Form\Handler\SalesChannelGroupHandler;
use Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelGroupRepository;

class SalesChannelGroupHandlerTest extends TestCase
{
    /**
     * @var FormInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $form;

    /**
     * @var ObjectManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $manager;

    /**
     * @var SalesChannelGroupHandler
     */
    protected $salesChannelGroupHandler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->form = $this->createMock(FormInterface::class);
        $this->manager = $this->createMock(ObjectManager::class);
        $this->salesChannelGroupHandler = new SalesChannelGroupHandler($this->manager);
    }

    public function testProcess()
    {
        /** @var SalesChannelGroup|\PHPUnit\Framework\MockObject\MockObject $updatedGroup */
        $updatedGroup = $this->createMock(SalesChannelGroup::class);
        /** @var SalesChannelGroup|\PHPUnit\Framework\MockObject\MockObject $systemGroup */
        $systemGroup = $this->createMock(SalesChannelGroup::class);

        $channel1 = $this->mockSalesChannel($systemGroup);
        $channel2 = $this->mockSalesChannel($updatedGroup);
        $channel3 = $this->mockSalesChannel($updatedGroup);

        $channelsBefore = [$channel1, $channel2];
        $channelsAfter = [$channel2, $channel3];

        $updatedGroup
            ->expects(static::exactly(2))
            ->method('getSalesChannels')
            ->willReturnOnConsecutiveCalls(new ArrayCollection($channelsBefore), new ArrayCollection($channelsAfter));

        $repository = $this->createMock(SalesChannelGroupRepository::class);
        $repository
            ->expects(static::once())
            ->method('findSystemChannelGroup')
            ->willReturn($systemGroup);

        $this->manager
            ->expects(static::once())
            ->method('getRepository')
            ->with(SalesChannelGroup::class)
            ->willReturn($repository);

        $this->manager
            ->expects(static::exactly(4))
            ->method('persist')
            ->withConsecutive(
                [$channel1],
                [$channel2],
                [$channel3],
                [$updatedGroup]
            );
        $this->manager
            ->expects(static::once())
            ->method('flush');

        /** @var Request|\PHPUnit\Framework\MockObject\MockObject $request */
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request
            ->expects(static::once())
            ->method('getMethod')
            ->willReturn('POST');
        $request
            ->expects(static::once())
            ->method('getMethod')
            ->willReturn('POST');
        $request->request = new ParameterBag();
        $request->files = new ParameterBag();
        $this->form
            ->expects(static::once())
            ->method('setData')
            ->with($updatedGroup);
        $this->form
            ->expects(static::once())
            ->method('submit')
            ->with([]);
        $this->form
            ->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $this->salesChannelGroupHandler->process($updatedGroup, $this->form, $request);
    }

    /**
     * @param MockObject $group
     * @return MockObject
     */
    private function mockSalesChannel(MockObject $group)
    {
        $salesChannel = $this->createMock(SalesChannel::class);
        $salesChannel
            ->expects(static::once())
            ->method('setGroup')
            ->with($group);

        return $salesChannel;
    }
}
