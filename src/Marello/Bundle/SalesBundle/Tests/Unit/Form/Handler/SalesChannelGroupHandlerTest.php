<?php

namespace Marello\Bundle\SalesBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\Form\Handler\SalesChannelGroupHandler;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

class SalesChannelGroupHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FormInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $form;

    /**
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    /**
     * @var SalesChannelGroupHandler
     */
    protected $salesChannelGroupHandler;

    protected function setUp()
    {
        $this->form = $this->createMock(FormInterface::class);
        $this->manager = $this->createMock(ObjectManager::class);
        $this->salesChannelGroupHandler = new SalesChannelGroupHandler($this->form, $this->manager);
    }

    public function testProcess()
    {
        /** @var SalesChannelGroup|\PHPUnit_Framework_MockObject_MockObject $updatedGroup */
        $updatedGroup = $this->createMock(SalesChannelGroup::class);
        /** @var SalesChannelGroup|\PHPUnit_Framework_MockObject_MockObject $systemGroup */
        $systemGroup = $this->createMock(SalesChannelGroup::class);

        $channel1 = $this->mockSalesChannel($systemGroup);
        $channel2 = $this->mockSalesChannel($updatedGroup);
        $channel3 = $this->mockSalesChannel($updatedGroup);

        $channelsBefore = [$channel1, $channel2];
        $channelsAfter = [$channel2, $channel3];

        $updatedGroup
            ->expects(static::at(0))
            ->method('getSalesChannels')
            ->willReturn(new ArrayCollection($channelsBefore));
        $updatedGroup
            ->expects(static::at(1))
            ->method('getSalesChannels')
            ->willReturn(new ArrayCollection($channelsAfter));

        $repository = $this->createMock(EntityRepository::class);
        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(['system' => true])
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

        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request
            ->expects(static::once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->form
            ->expects(static::once())
            ->method('setData')
            ->with($updatedGroup);
        $this->form
            ->expects(static::once())
            ->method('submit')
            ->with($request);
        $this->form
            ->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $this->salesChannelGroupHandler->process($updatedGroup, $request);
    }

    public function testGetFormView()
    {
        $formView = $this->createMock(FormView::class);

        $form = $this->createMock(FormInterface::class);
        $form
            ->expects(static::once())
            ->method('createView')
            ->willReturn($formView);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory
            ->expects(static::once())
            ->method('createNamed')
            ->willReturn($form);

        $formType = $this->createMock(FormTypeInterface::class);
        $formType
            ->expects(static::once())
            ->method('getName')
            ->willReturn('name');

        $config = $this->createMock(FormConfigInterface::class);
        $config
            ->expects(static::once())
            ->method('getFormFactory')
            ->willReturn($formFactory);
        $config
            ->expects(static::once())
            ->method('getType')
            ->willReturn($formType);

        $this->form
            ->expects(static::once())
            ->method('getConfig')
            ->willReturn($config);
        $this->form
            ->expects(static::once())
            ->method('getName')
            ->willReturn('name');
        $this->form
            ->expects(static::once())
            ->method('getData')
            ->willReturn([]);

        static::assertEquals($formView, $this->salesChannelGroupHandler->getFormView());
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $group
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockSalesChannel(\PHPUnit_Framework_MockObject_MockObject $group)
    {
        $salesChannel = $this->createMock(SalesChannel::class);
        $salesChannel
            ->expects(static::once())
            ->method('setGroup')
            ->with($group);

        return $salesChannel;
    }
}
