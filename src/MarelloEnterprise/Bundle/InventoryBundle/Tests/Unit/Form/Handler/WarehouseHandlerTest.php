<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseGroupRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Handler\WarehouseHandler;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class WarehouseHandlerTest extends \PHPUnit_Framework_TestCase
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
     * @var Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var Warehouse|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entity;

    /**
     * @var WarehouseHandler
     */
    protected $warehouseHandler;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->form = $this->createMock(FormInterface::class);
        $this->manager = $this->createMock(ObjectManager::class);
        $this->entity = $this->createMock(Warehouse::class);

        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request
            ->expects(static::once())
            ->method('getMethod')
            ->willReturn('POST');
        $organization = $this->createMock(OrganizationInterface::class);
        $this->entity
            ->expects(static::any())
            ->method('getOwner')
            ->willReturn($organization);

        $this->form
            ->expects(static::once())
            ->method('setData')
            ->with($this->entity);
        $this->form
            ->expects(static::once())
            ->method('submit')
            ->with($this->request);
        $this->form
            ->expects(static::once())
            ->method('isValid')
            ->willReturn(true);
        $this->form
            ->expects(static::once())
            ->method('has')
            ->with('createOwnGroup')
            ->willReturn(true);
        $childForm = $this->createMock(FormInterface::class);
        $childForm
            ->expects(static::once())
            ->method('getData')
            ->willReturn(true);
        $this->form
            ->expects(static::once())
            ->method('get')
            ->with('createOwnGroup')
            ->willReturn($childForm);

        $this->warehouseHandler = new WarehouseHandler($this->manager);
    }

    public function testProcessFixedBefore()
    {
        /** @var Warehouse|\PHPUnit_Framework_MockObject_MockObject $group */
        $group = $this->createMock(WarehouseGroup::class);

        $typeBefore = $this->getEntity(
            WarehouseType::class,
            [
                'name' => WarehouseTypeProviderInterface::WAREHOUSE_TYPE_FIXED
            ]
        );
        $typeAfter = $this->getEntity(
            WarehouseType::class,
            [
                'name' => WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL
            ]
        );

        $this->entity
            ->expects(static::at(0))
            ->method('getWarehouseType')
            ->willReturn($typeBefore);
        $this->entity
            ->expects(static::at(1))
            ->method('getWarehouseType')
            ->willReturn($typeAfter);
        $this->entity
            ->expects(static::once())
            ->method('getGroup')
            ->willReturn($group);

        $repository = $this->createMock(WarehouseGroupRepository::class);
        $repository
            ->expects(static::once())
            ->method('findSystemWarehouseGroup')
            ->willReturn($group);

        $this->manager
            ->expects(static::once())
            ->method('getRepository')
            ->with(WarehouseGroup::class)
            ->willReturn($repository);
        $this->manager
            ->expects(static::once())
            ->method('remove');
        $this->manager
            ->expects(static::once())
            ->method('persist');
        $this->manager
            ->expects(static::once())
            ->method('flush');

        $this->entity
            ->expects(static::exactly(2))
            ->method('setGroup');

        $this->warehouseHandler->process($this->entity, $this->form, $this->request);
    }

    public function testProcessFixedAfterNotSystemGroup()
    {
        /** @var Warehouse|\PHPUnit_Framework_MockObject_MockObject $group */
        $group = $this->createMock(WarehouseGroup::class);
        $group
            ->expects(static::any())
            ->method('isSystem')
            ->willReturn(false);
        $group
            ->expects(static::once())
            ->method('getWarehouses')
            ->willReturn(new ArrayCollection([new Warehouse()]));
        $group
            ->expects(static::once())
            ->method('setName')
            ->with('label')
            ->willReturnSelf();
        $group
            ->expects(static::once())
            ->method('setDescription')
            ->with('label group')
            ->willReturnSelf();

        $typeBefore = $this->getEntity(
            WarehouseType::class,
            [
                'name' => WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL
            ]
        );
        $typeAfter = $this->getEntity(
            WarehouseType::class,
            [
                'name' => WarehouseTypeProviderInterface::WAREHOUSE_TYPE_FIXED
            ]
        );

        $this->entity
            ->expects(static::at(0))
            ->method('getWarehouseType')
            ->willReturn($typeBefore);
        $this->entity
            ->expects(static::at(2))
            ->method('getWarehouseType')
            ->willReturn($typeAfter);
        $this->entity
            ->expects(static::once())
            ->method('getGroup')
            ->willReturn($group);
        $this->entity
            ->expects(static::once())
            ->method('getLabel')
            ->willReturn('label');

        $this->manager
            ->expects(static::exactly(2))
            ->method('persist');
        $this->manager
            ->expects(static::exactly(2))
            ->method('flush');

        $this->entity
            ->expects(static::once())
            ->method('setGroup')
            ->willReturnSelf();

        $this->warehouseHandler->process($this->entity, $this->form, $this->request);
    }

    public function testProcessFixedAfterSystemGroup()
    {
        /** @var Warehouse|\PHPUnit_Framework_MockObject_MockObject $group */
        $group = $this->createMock(WarehouseGroup::class);
        $group
            ->expects(static::once())
            ->method('isSystem')
            ->willReturn(true);

        $typeBefore = $this->getEntity(
            WarehouseType::class,
            [
                'name' => WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL
            ]
        );
        $typeAfter = $this->getEntity(
            WarehouseType::class,
            [
                'name' => WarehouseTypeProviderInterface::WAREHOUSE_TYPE_FIXED
            ]
        );

        $this->entity
            ->expects(static::at(0))
            ->method('getWarehouseType')
            ->willReturn($typeBefore);
        $this->entity
            ->expects(static::at(2))
            ->method('getWarehouseType')
            ->willReturn($typeAfter);
        $this->entity
            ->expects(static::once())
            ->method('getGroup')
            ->willReturn($group);
        $this->entity
            ->expects(static::exactly(2))
            ->method('getLabel')
            ->willReturn('label');

        $this->manager
            ->expects(static::exactly(2))
            ->method('persist');
        $this->manager
            ->expects(static::exactly(2))
            ->method('flush');

        $this->entity
            ->expects(static::once())
            ->method('setGroup')
            ->willReturnSelf();

        $organization = $this->createMock(OrganizationInterface::class);
        $this->entity
            ->expects(static::once())
            ->method('getOwner')
            ->willReturn($organization);

        $this->warehouseHandler->process($this->entity, $this->form, $this->request);
    }
}
