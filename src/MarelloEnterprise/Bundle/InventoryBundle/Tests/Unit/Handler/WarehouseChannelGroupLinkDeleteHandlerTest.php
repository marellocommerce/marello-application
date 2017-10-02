<?php

namespace Marello\Bundle\ReturnBundle\Tests\Unit\Validator;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use MarelloEnterprise\Bundle\InventoryBundle\Handler\WarehouseChannelGroupLinkDeleteHandler;
use Oro\Bundle\OrganizationBundle\Ownership\OwnerDeletionManager;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class WarehouseChannelGroupLinkDeleteHandlerTest extends TestCase
{
    /**
     * @var SecurityFacade|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $securityFacade;

    /**
     * @var OwnerDeletionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ownerDeletionManager;

    /**
     * @var ApiEntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $apiEntityManager;

    /**
     * @var WarehouseChannelGroupLinkDeleteHandler
     */
    protected $handler;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

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

        $this->handler = new WarehouseChannelGroupLinkDeleteHandler($this->securityFacade);
        $this->handler->setOwnerDeletionManager($this->ownerDeletionManager);
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

        $this->handler->handleDelete($nonExistingWarehouseId, $this->apiEntityManager);
    }

    /**
     * @expectedException \Oro\Bundle\SecurityBundle\Exception\ForbiddenException
     * @expectedExceptionMessage An operation is forbidden. Reason: has assignments
     */
    public function testHandlerThrowsForbiddenExceptionByHasAssignments()
    {
        $warehouseId = 0;

        $warehouseGroupMock = $this->createMock(WarehouseGroup::class);

        $this->apiEntityManager->expects($this->once())
            ->method('find')
            ->with($warehouseId)
            ->willReturn($warehouseGroupMock);

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

        $this->handler->handleDelete($warehouseId, $this->apiEntityManager);
    }

    /**
     * @expectedException \Oro\Bundle\SecurityBundle\Exception\ForbiddenException
     * @expectedExceptionMessage You have no rights to delete this entity
     */
    public function testHandlerThrowsForbiddenExceptionByNoRightsToDeleteEntity()
    {
        $warehouseId = 0;

        $warehouseGroupMock = $this->createMock(WarehouseGroup::class);

        $this->apiEntityManager->expects($this->once())
            ->method('find')
            ->with($warehouseId)
            ->willReturn($warehouseGroupMock);

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

        $this->handler->handleDelete($warehouseId, $this->apiEntityManager);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage An operation is forbidden.
     * Reason: It is forbidden to delete system Warehouse Channel Group Link
     */
    public function testHandlerThrowsForbiddenExceptionByNotPossibleToDeleteSystemLink()
    {
        $warehouseId = 0;

        $warehouseGroupMock = $this->createMock(WarehouseGroup::class);

        $this->apiEntityManager->expects($this->once())
            ->method('find')
            ->with($warehouseId)
            ->willReturn($warehouseGroupMock);

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

        $warehouseGroupMock->expects($this->once())
            ->method('isSystem')
            ->willReturn(true);

        $this->handler->handleDelete($warehouseId, $this->apiEntityManager);
    }

    public function testHandlerLinkCanBeDeleted()
    {
        $warehouseId = 0;

        $warehouseGroupMock = $this->createMock(WarehouseGroup::class);

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiEntityManager->expects($this->once())
            ->method('find')
            ->with($warehouseId)
            ->willReturn($warehouseGroupMock);

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

        $this->handler->handleDelete($warehouseId, $this->apiEntityManager);
    }
}
