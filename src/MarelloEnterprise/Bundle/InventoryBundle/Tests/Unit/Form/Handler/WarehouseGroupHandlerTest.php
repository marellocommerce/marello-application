<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Entity\Stub\WarehouseStub;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseGroupRepository;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Handler\WarehouseGroupHandler;

class WarehouseGroupHandlerTest extends TestCase
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
     * @var AclHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $aclHelper;

    /**
     * @var WarehouseGroupHandler
     */
    protected $warehouseGroupHandler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->form = $this->createMock(FormInterface::class);
        $this->manager = $this->createMock(ObjectManager::class);
        $this->aclHelper = $this->createMock(AclHelper::class);
        $this->warehouseGroupHandler = new WarehouseGroupHandler($this->manager, $this->aclHelper);
    }

    public function testProcess()
    {
        /** @var WarehouseGroup|\PHPUnit\Framework\MockObject\MockObject $updatedGroup */
        $updatedGroup = $this->createMock(WarehouseGroup::class);
        /** @var WarehouseGroup|\PHPUnit\Framework\MockObject\MockObject $systemGroup */
        $systemGroup = $this->createMock(WarehouseGroup::class);

        $wh1 = $this->mockWarehouse($systemGroup);
        $wh2 = $this->mockWarehouse($updatedGroup);
        $wh3 = $this->mockWarehouse($updatedGroup);

        $whBefore = [$wh1, $wh2];
        $whAfter = [$wh2, $wh3];

        $updatedGroup
            ->expects(static::exactly(2))
            ->method('getWarehouses')
            ->willReturnOnConsecutiveCalls(new ArrayCollection($whBefore), new ArrayCollection($whAfter));

        $repository = $this->createMock(WarehouseGroupRepository::class);
        $repository
            ->expects(static::once())
            ->method('findSystemWarehouseGroup')
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

        /** @var Request|\PHPUnit\Framework\MockObject\MockObject $request */
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request
            ->expects(static::once())
            ->method('getMethod')
            ->willReturn('POST');
        $request->request = new ParameterBag([]);
        $request->files = new ParameterBag([]);
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

        $this->warehouseGroupHandler->process($updatedGroup, $this->form, $request);
    }

    /**
     * @param MockObject $group
     * @return WarehouseStub
     */
    private function mockWarehouse(MockObject $group)
    {
        $warehouse = new WarehouseStub();
        $warehouse->setGroup($group);
        return $warehouse;
    }
}
