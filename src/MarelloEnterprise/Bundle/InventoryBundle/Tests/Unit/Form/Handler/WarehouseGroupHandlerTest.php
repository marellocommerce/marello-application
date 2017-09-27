<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Handler\WarehouseGroupHandler;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

class WarehouseGroupHandlerTest extends \PHPUnit_Framework_TestCase
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
     * @var WarehouseGroupHandler
     */
    protected $warehouseGroupHandler;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->form = $this->createMock(FormInterface::class);
        $this->manager = $this->createMock(ObjectManager::class);
        $this->warehouseGroupHandler = new WarehouseGroupHandler($this->form, $this->manager);
    }

    public function testProcess()
    {
        /** @var WarehouseGroup|\PHPUnit_Framework_MockObject_MockObject $updatedGroup */
        $updatedGroup = $this->createMock(WarehouseGroup::class);
        /** @var WarehouseGroup|\PHPUnit_Framework_MockObject_MockObject $systemGroup */
        $systemGroup = $this->createMock(WarehouseGroup::class);

        $wh1 = $this->mockWarehouse($systemGroup);
        $wh2 = $this->mockWarehouse($updatedGroup);
        $wh3 = $this->mockWarehouse($updatedGroup);

        $whBefore = [$wh1, $wh2];
        $whAfter = [$wh2, $wh3];

        $updatedGroup
            ->expects(static::at(0))
            ->method('getWarehouses')
            ->willReturn(new ArrayCollection($whBefore));
        $updatedGroup
            ->expects(static::at(1))
            ->method('getWarehouses')
            ->willReturn(new ArrayCollection($whAfter));

        $repository = $this->createMock(EntityRepository::class);
        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(['system' => true])
            ->willReturn($systemGroup);

        $this->manager
            ->expects(static::once())
            ->method('getRepository')
            ->with(WarehouseGroup::class)
            ->willReturn($repository);

        $this->manager
            ->expects(static::exactly(4))
            ->method('persist')
            ->withConsecutive(
                [$wh1],
                [$wh2],
                [$wh3],
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

        $this->warehouseGroupHandler->process($updatedGroup, $request);
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

        static::assertEquals($formView, $this->warehouseGroupHandler->getFormView());
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $group
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockWarehouse(\PHPUnit_Framework_MockObject_MockObject $group)
    {
        $salesChannel = $this->createMock(Warehouse::class);
        $salesChannel
            ->expects(static::once())
            ->method('setGroup')
            ->with($group);

        return $salesChannel;
    }
}
