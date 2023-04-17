<?php

namespace Marello\Bundle\NotificationAlertBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\NotificationAlertBundle\Datagrid\ActionPermissionProvider;
use Marello\Bundle\NotificationAlertBundle\Entity\NotificationAlert;
use Marello\Bundle\NotificationAlertBundle\Entity\Repository\NotificationAlertRepository;
use Marello\Bundle\NotificationAlertBundle\Event\CreateNotificationAlertEvent;
use Marello\Bundle\NotificationAlertBundle\EventListener\CreateNotificationAlertEventListener;
use Marello\Bundle\NotificationAlertBundle\Factory\NotificationAlertContextFactory;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertResolvedInterface;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertSourceInterface;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertTypeInterface;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateNotificationAlertEventListenerTest extends TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

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

        $this->listener = new CreateNotificationAlertEventListener($this->registry, $this->translator);
    }

    public function testOnCreateNew(): void
    {
        $entity = new \stdClass();
        $context = NotificationAlertContextFactory::createError(
            NotificationAlertSourceInterface::NOTIFICATION_ALERT_SOURCE_WEBHOOK,
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
            ->with(NotificationAlert::class)
            ->willReturn($em);
        $notAlertRepo = $this->createMock(NotificationAlertRepository::class);
        $enumRepo = $this->createMock(EnumValueRepository::class);
        $em->expects($this->exactly(4))
            ->method('getRepository')
            ->withConsecutive(
                [NotificationAlert::class],
                [ExtendHelper::buildEnumValueClassName(
                    NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_ENUM_CODE
                )],
                [ExtendHelper::buildEnumValueClassName(
                    NotificationAlertTypeInterface::NOTIFICATION_ALERT_TYPE_ENUM_CODE
                )],
                [ExtendHelper::buildEnumValueClassName(
                    NotificationAlertSourceInterface::NOTIFICATION_ALERT_SOURCE_ENUM_CODE
                )],
            )
            ->willReturnOnConsecutiveCalls($notAlertRepo, $enumRepo, $enumRepo, $enumRepo);
        $notAlertRepo->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);
        $enumRepo->expects($this->exactly(3))
            ->method('find')
            ->willReturn(new TestEnumValue('test', 'Test'));
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $event = new CreateNotificationAlertEvent($context);
        $this->listener->onCreate($event);
    }

    public function testOnCreateOld(): void
    {
        $entity = new \stdClass();
        $context = NotificationAlertContextFactory::createError(
            NotificationAlertSourceInterface::NOTIFICATION_ALERT_SOURCE_WEBHOOK,
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
            ->with(NotificationAlert::class)
            ->willReturn($em);
        $notAlertRepo = $this->createMock(NotificationAlertRepository::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->with(NotificationAlert::class)
            ->willReturn($notAlertRepo);
        $notificationAlert = new NotificationAlert();
        $notAlertRepo->expects($this->once())
            ->method('findOneBy')
            ->willReturn($notificationAlert);
        $em->expects($this->never())->method('persist');
        $em->expects($this->once())->method('flush');

        $event = new CreateNotificationAlertEvent($context);
        $this->listener->onCreate($event);
        $this->assertEquals(2, $notificationAlert->getCount());
    }
}
