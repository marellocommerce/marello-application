<?php

namespace Marello\Bundle\NotificationMessageBundle\Tests\Unit\EventListener;

use Symfony\Contracts\Translation\TranslatorInterface;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;

use Marello\Bundle\NotificationMessageBundle\Entity\NotificationMessage;
use Marello\Bundle\NotificationMessageBundle\Datagrid\ActionPermissionProvider;
use Marello\Bundle\NotificationMessageBundle\Event\CreateNotificationMessageEvent;
use Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageTypeInterface;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageSourceInterface;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface;
use Marello\Bundle\NotificationMessageBundle\Entity\Repository\NotificationMessageRepository;
use Marello\Bundle\NotificationMessageBundle\EventListener\NotificationMessageEventListener;

class NotificationMessageEventListenerTest extends TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var ActionPermissionProvider */
    private $listener;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(function ($message, $arguments, $domain) {
                return $message . $domain;
            });

        $this->configManager = $this->createMock(ConfigManager::class);
        $this->listener = new NotificationMessageEventListener($this->registry, $this->configManager, $this->translator);
    }

    public function testOnCreateNew(): void
    {
        $entity = new \stdClass();
        $context = NotificationMessageContextFactory::createError(
            NotificationMessageSourceInterface::NOTIFICATION_MESSAGE_SOURCE_ALLOCATION,
            'test_title',
            'test_message',
            'test_solution',
            $entity
        );
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->expects($this->once())
            ->method('getIdentifierValues')
            ->with($entity)
            ->willReturn(['id' => 1]);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with(get_class($entity))
            ->willReturn($classMetadata);
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with(NotificationMessage::class)
            ->willReturn($em);
        $notMessageRepo = $this->createMock(NotificationMessageRepository::class);
        $enumRepo = $this->createMock(EnumValueRepository::class);
        $em->expects($this->exactly(4))
            ->method('getRepository')
            ->withConsecutive(
                [NotificationMessage::class],
                [ExtendHelper::buildEnumValueClassName(
                    NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_ENUM_CODE
                )],
                [ExtendHelper::buildEnumValueClassName(
                    NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_ENUM_CODE
                )],
                [ExtendHelper::buildEnumValueClassName(
                    NotificationMessageSourceInterface::NOTIFICATION_MESSAGE_SOURCE_ENUM_CODE
                )],
            )
            ->willReturnOnConsecutiveCalls($notMessageRepo, $enumRepo, $enumRepo, $enumRepo);
        $notMessageRepo->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);
        $enumRepo->expects($this->exactly(3))
            ->method('find')
            ->willReturn(new TestEnumValue('test', 'Test'));
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $event = new CreateNotificationMessageEvent($context);
        $this->listener->onCreate($event);
    }

    public function testOnCreateOld(): void
    {
        $entity = new \stdClass();
        $context = NotificationMessageContextFactory::createError(
            NotificationMessageSourceInterface::NOTIFICATION_MESSAGE_SOURCE_ALLOCATION,
            'test_title',
            'test_message',
            'test_solution',
            $entity
        );
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->expects($this->once())
            ->method('getIdentifierValues')
            ->with($entity)
            ->willReturn(['id' => 1]);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with(get_class($entity))
            ->willReturn($classMetadata);
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with(NotificationMessage::class)
            ->willReturn($em);
        $notMessageRepo = $this->createMock(NotificationMessageRepository::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->with(NotificationMessage::class)
            ->willReturn($notMessageRepo);
        $notificationMessage = new NotificationMessage();
        $notMessageRepo->expects($this->once())
            ->method('findOneBy')
            ->willReturn($notificationMessage);
        $em->expects($this->never())->method('persist');
        $em->expects($this->once())->method('flush');

        $event = new CreateNotificationMessageEvent($context);
        $this->listener->onCreate($event);
        $this->assertEquals(2, $notificationMessage->getCount());
    }
}
