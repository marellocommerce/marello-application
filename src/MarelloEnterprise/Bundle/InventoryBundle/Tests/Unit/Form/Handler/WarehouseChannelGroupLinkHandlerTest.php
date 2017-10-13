<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseChannelGroupLinkRepository;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Handler\WarehouseChannelGroupLinkHandler;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class WarehouseChannelGroupLinkHandlerTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var FormInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $form;

    /**
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    /**
     * @var WarehouseChannelGroupLinkHandler
     */
    protected $handler;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->form = $this->createMock(FormInterface::class);
        $this->manager = $this->createMock(ObjectManager::class);
        $this->handler = new WarehouseChannelGroupLinkHandler($this->manager);
    }

    public function testProcess()
    {
        /** @var WarehouseChannelGroupLink|\PHPUnit_Framework_MockObject_MockObject $updatedLink */
        $updatedLink = $this->createMock(WarehouseChannelGroupLink::class);
        /** @var WarehouseChannelGroupLink|\PHPUnit_Framework_MockObject_MockObject $systemLink */
        $systemLink = $this->createMock(WarehouseChannelGroupLink::class);

        $chg1 = $this->getEntity(SalesChannelGroup::class, ['id' => 1, 'name' => 'group1']);
        $chg2 = $this->getEntity(SalesChannelGroup::class, ['id' => 2, 'name' => 'group2']);
        $chg3 = $this->getEntity(SalesChannelGroup::class, ['id' => 3, 'name' => 'group3']);

        $chgBefore = [$chg1, $chg2];
        $chgAfter = [$chg2, $chg3];

        $updatedLink
            ->expects(static::at(0))
            ->method('getSalesChannelGroups')
            ->willReturn(new ArrayCollection($chgBefore));
        $updatedLink
            ->expects(static::at(1))
            ->method('getSalesChannelGroups')
            ->willReturn(new ArrayCollection($chgAfter));

        $systemLink
            ->expects(static::once())
            ->method('removeSalesChannelGroup')
            ->with($chg3);
        $systemLink
            ->expects(static::once())
            ->method('addSalesChannelGroup')
            ->with($chg1);

        $repository = $this->createMock(WarehouseChannelGroupLinkRepository::class);
        $repository
            ->expects(static::once())
            ->method('findSystemLink')
            ->willReturn($systemLink);

        $this->manager
            ->expects(static::once())
            ->method('getRepository')
            ->with(WarehouseChannelGroupLink::class)
            ->willReturn($repository);

        $this->manager
            ->expects(static::exactly(3))
            ->method('persist')
            ->withConsecutive(
                [$systemLink],
                [$updatedLink],
                [$systemLink]
            );
        $this->manager
            ->expects(static::exactly(3))
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
            ->with($updatedLink);
        $this->form
            ->expects(static::once())
            ->method('submit')
            ->with($request);
        $this->form
            ->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $this->handler->process($updatedLink, $this->form, $request);
    }
}
