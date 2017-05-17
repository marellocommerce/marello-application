<?php

namespace Marello\Bundle\ReturnBundle\Tests\Unit\Validator;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\OrganizationBundle\Ownership\OwnerDeletionManager;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Handler\WarehouseDeleteHandler;
use MarelloEnterprise\Bundle\InventoryBundle\Validator\WarehouseDeleteValidator;

class WarehouseDeleteHandlerTest extends TestCase
{
    /** @var  WarehouseDeleteValidator $warehouseDeleteValidator */
    protected $warehouseDeleteValidator;

    /** @var SecurityFacade $securityFacade */
    protected $securityFacade;

    /** @var WarehouseDeleteHandler $warehouseDeleteHandler */
    protected $warehouseDeleteHandler;

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

        $this->warehouseDeleteValidator = $this
            ->getMockBuilder(WarehouseDeleteValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->warehouseDeleteHandler =
            new WarehouseDeleteHandler($this->warehouseDeleteValidator, $this->securityFacade);
    }

    /**
     * @expectedException \Doctrine\ORM\EntityNotFoundException
     */
    public function testHandlerThrowsEntityNotFoundException()
    {
        $nonExistingWarehouseId = 0;
        $apiEntityManager = $this->getMockBuilder(ApiEntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $apiEntityManager->expects($this->once())
            ->method('find')
            ->with($nonExistingWarehouseId);

        $this->warehouseDeleteHandler->handleDelete($nonExistingWarehouseId, $apiEntityManager);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testHandlerThrowsAccessDeniedAcception()
    {
        $warehouseId = 0;
        $apiEntityManagerMock = $this->getMockBuilder(ApiEntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $warehouseMock = $this->getMockBuilder(Warehouse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $apiEntityManagerMock->expects($this->once())
            ->method('find')
            ->with($warehouseId)
            ->willReturn($warehouseMock);

        $this->securityFacade->expects($this->once())
            ->method('isGranted')
            ->willReturn(false);

        $this->warehouseDeleteHandler->handleDelete($warehouseId, $apiEntityManagerMock);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot delete default warehouse.
     * @expectedExceptionCode 500
     */
    public function testHandlerWillThrowException()
    {
        $warehouseId = 0;
        $apiEntityManagerMock = $this->getMockBuilder(ApiEntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $warehouseMock = $this->getMockBuilder(Warehouse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $apiEntityManagerMock->expects($this->once())
            ->method('find')
            ->with($warehouseId)
            ->willReturn($warehouseMock);

        $this->securityFacade->expects($this->once())
            ->method('isGranted')
            ->willReturn(true);

        $this->warehouseDeleteValidator->expects($this->once())
            ->method('validate')
            ->with($warehouseMock)
            ->willReturn(false);

        $this->warehouseDeleteHandler->handleDelete($warehouseId, $apiEntityManagerMock);
    }

    /**
     * {@inheritdoc}
     */
    public function testHandlerWarehouseCanBeDeleted()
    {
        $warehouseId = 0;

        $ownerDeletionManagerMock = $this->getMockBuilder(OwnerDeletionManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $apiEntityManagerMock = $this->getMockBuilder(ApiEntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $warehouseMock = $this->getMockBuilder(Warehouse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $apiEntityManagerMock->expects($this->once())
            ->method('find')
            ->with($warehouseId)
            ->willReturn($warehouseMock);

        $this->securityFacade->expects($this->once())
            ->method('isGranted')
            ->willReturn(true);

        $this->warehouseDeleteValidator->expects($this->once())
            ->method('validate')
            ->with($warehouseMock)
            ->willReturn(true);

        $apiEntityManagerMock->expects($this->atLeastOnce())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $objectManagerMock->expects($this->atLeastOnce())
            ->method('remove');

        $ownerDeletionManagerMock->expects($this->atLeastOnce())
            ->method('isOwner')
            ->willReturn(false);

        $objectManagerMock->expects($this->atLeastOnce())
            ->method('flush');

        // set owner deletion manager
        $this->warehouseDeleteHandler->setOwnerDeletionManager($ownerDeletionManagerMock);

        $this->warehouseDeleteHandler->handleDelete($warehouseId, $apiEntityManagerMock);
    }
}
