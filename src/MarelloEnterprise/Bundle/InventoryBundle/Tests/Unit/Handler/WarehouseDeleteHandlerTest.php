<?php

namespace Marello\Bundle\ReturnBundle\Tests\Unit\Validator;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use MarelloEnterprise\Bundle\InventoryBundle\Handler\WarehouseDeleteHandler;
use Oro\Bundle\OrganizationBundle\Ownership\OwnerDeletionManager;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class WarehouseDeleteHandlerTest extends TestCase
{
    /**
     * @var SecurityFacade|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $securityFacade;

    /**
     * @var ApiEntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $apiEntityManager;

    /**
     * @var WarehouseDeleteHandler
     */
    protected $warehouseDeleteHandler;

    /**
     * @var OwnerDeletionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ownerDeletionManager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->securityFacade = $this
            ->getMockBuilder(SecurityFacade::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiEntityManager = $this->getMockBuilder(ApiEntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->ownerDeletionManager = $this->getMockBuilder(OwnerDeletionManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->warehouseDeleteHandler = new WarehouseDeleteHandler($this->securityFacade);
        $this->warehouseDeleteHandler->setOwnerDeletionManager($this->ownerDeletionManager);
    }

    /**
     * @expectedException \Doctrine\ORM\EntityNotFoundException
     */
    public function testHandlerThrowsEntityNotFoundException()
    {
        $nonExistingWarehouseId = 0;

        $this->apiEntityManager->expects($this->once())
            ->method('find')
            ->with($nonExistingWarehouseId);

        $this->warehouseDeleteHandler->handleDelete($nonExistingWarehouseId, $this->apiEntityManager);
    }

    /**
     * @expectedException \Oro\Bundle\SecurityBundle\Exception\ForbiddenException
     * @expectedExceptionMessage An operation is forbidden. Reason: has assignments
     */
    public function testHandlerThrowsForbiddenExceptionByHasAssignments()
    {
        $warehouseId = 0;

        $warehouseMock = $this->getMockBuilder(Warehouse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiEntityManager->expects($this->once())
            ->method('find')
            ->with($warehouseId)
            ->willReturn($warehouseMock);

        $om = $this->createMock(ObjectManager::class);

        $this->apiEntityManager->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($om);

        $this->ownerDeletionManager->expects($this->once())
            ->method('isOwner')
            ->willReturn(true);

        $this->ownerDeletionManager->expects($this->once())
            ->method('hasAssignments')
            ->willReturn(true);

        $this->warehouseDeleteHandler->handleDelete($warehouseId, $this->apiEntityManager);
    }

    /**
     * @expectedException \Oro\Bundle\SecurityBundle\Exception\ForbiddenException
     * @expectedExceptionMessage You have no rights to delete this entity
     */
    public function testHandlerThrowsForbiddenExceptionByNoRightsToDeleteEntity()
    {
        $warehouseId = 0;

        $warehouseMock = $this->getMockBuilder(Warehouse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiEntityManager->expects($this->once())
            ->method('find')
            ->with($warehouseId)
            ->willReturn($warehouseMock);

        $om = $this->createMock(ObjectManager::class);

        $this->apiEntityManager->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($om);

        $this->securityFacade->expects($this->once())
            ->method('isGranted')
            ->willReturn(false);

        $this->ownerDeletionManager->expects($this->once())
            ->method('isOwner')
            ->willReturn(false);

        $this->warehouseDeleteHandler->handleDelete($warehouseId, $this->apiEntityManager);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage An operation is forbidden. Reason: It is forbidden to delete default Warehouse
     */
    public function testHandlerThrowsForbiddenExceptionByNotPossibleToDeleteDefaultWarehouse()
    {
        $warehouseId = 0;

        $warehouseMock = $this->getMockBuilder(Warehouse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiEntityManager->expects($this->once())
            ->method('find')
            ->with($warehouseId)
            ->willReturn($warehouseMock);

        $om = $this->createMock(ObjectManager::class);

        $this->apiEntityManager->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($om);

        $this->securityFacade->expects($this->once())
            ->method('isGranted')
            ->willReturn(true);

        $this->ownerDeletionManager->expects($this->once())
            ->method('isOwner')
            ->willReturn(false);

        $warehouseMock->expects($this->once())
            ->method('isDefault')
            ->willReturn(true);

        $this->warehouseDeleteHandler->handleDelete($warehouseId, $this->apiEntityManager);
    }

    public function testHandlerWarehouseCanBeDeleted()
    {
        $warehouseId = 0;

        $warehouseMock = $this->getMockBuilder(Warehouse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiEntityManager->expects($this->once())
            ->method('find')
            ->with($warehouseId)
            ->willReturn($warehouseMock);

        $this->securityFacade->expects($this->once())
            ->method('isGranted')
            ->willReturn(true);

        $this->apiEntityManager->expects($this->atLeastOnce())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $objectManagerMock->expects($this->atLeastOnce())
            ->method('remove');

        $this->ownerDeletionManager->expects($this->atLeastOnce())
            ->method('isOwner')
            ->willReturn(false);

        $objectManagerMock->expects($this->atLeastOnce())
            ->method('flush');

        $this->warehouseDeleteHandler->handleDelete($warehouseId, $this->apiEntityManager);
    }
}
