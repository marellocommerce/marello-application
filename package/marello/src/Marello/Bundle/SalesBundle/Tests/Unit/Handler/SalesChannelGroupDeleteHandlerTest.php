<?php

namespace Marello\Bundle\SalesBundle\Tests\Unit\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\Handler\SalesChannelGroupDeleteHandler;
use Oro\Bundle\OrganizationBundle\Ownership\OwnerDeletionManager;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class SalesChannelGroupDeleteHandlerTest extends TestCase
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
     * @var SalesChannelGroupDeleteHandler
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

        $this->handler = new SalesChannelGroupDeleteHandler($this->securityFacade);
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
        $channelId = 0;

        $channelGroupMock = $this->createMock(SalesChannelGroup::class);

        $this->apiEntityManager->expects($this->once())
            ->method('find')
            ->with($channelId)
            ->willReturn($channelGroupMock);

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

        $this->handler->handleDelete($channelId, $this->apiEntityManager);
    }

    /**
     * @expectedException \Oro\Bundle\SecurityBundle\Exception\ForbiddenException
     * @expectedExceptionMessage You have no rights to delete this entity
     */
    public function testHandlerThrowsForbiddenExceptionByNoRightsToDeleteEntity()
    {
        $channelId = 0;

        $channelGroupMock = $this->createMock(SalesChannelGroup::class);

        $this->apiEntityManager->expects($this->once())
            ->method('find')
            ->with($channelId)
            ->willReturn($channelGroupMock);

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

        $this->handler->handleDelete($channelId, $this->apiEntityManager);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage An operation is forbidden.
     * Reason: It is forbidden to delete system Sales Channel Group
     */
    public function testHandlerThrowsForbiddenExceptionByNotPossibleToDeleteSystemLink()
    {
        $channelId = 0;

        $channelGroupMock = $this->createMock(SalesChannelGroup::class);

        $this->apiEntityManager->expects($this->once())
            ->method('find')
            ->with($channelId)
            ->willReturn($channelGroupMock);

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

        $channelGroupMock->expects($this->once())
            ->method('isSystem')
            ->willReturn(true);

        $this->handler->handleDelete($channelId, $this->apiEntityManager);
    }
}
