<?php

namespace Marello\Bundle\NotificationMessageBundle\Tests\Unit\Datagrid\Extension\MassAction;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\NotificationMessageBundle\Datagrid\Extension\MassAction\ResolveMassActionHandler;
use Marello\Bundle\NotificationMessageBundle\Entity\NotificationMessage;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\IterableResultInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerArgs;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResolveMassActionHandlerTest extends TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var ResolveMassActionHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnArgument(0);

        $this->handler = new ResolveMassActionHandler($this->registry, $this->translator);
    }

    public function testHandleNoResults(): void
    {
        $options = ActionConfiguration::create([
            'entity_name' => NotificationMessage::class,
            'data_identifier' => 'id',
        ]);
        $massAction = $this->createMock(MassActionInterface::class);
        $massAction->expects($this->once())
            ->method('getOptions')
            ->willReturn($options);
        $datagrid = $this->createMock(DatagridInterface::class);
        $results = $this->createMock(IterableResultInterface::class);
        $repository = $this->createMock(EnumValueRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with(NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_YES)
            ->willReturn(new TestEnumValue('test', 'Test'));
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with(NotificationMessage::class)
            ->willReturn($em);
        $em->expects($this->never())
            ->method('flush');

        $args = new MassActionHandlerArgs($massAction, $datagrid, $results);
        $result = $this->handler->handle($args);
        $this->assertEquals('marello.notificationmessage.mass_actions.resolve.no_items', $result->getMessage());
    }

    public function testHandle(): void
    {
        $options = ActionConfiguration::create([
            'entity_name' => NotificationMessage::class,
            'data_identifier' => 'id',
        ]);
        $massAction = $this->createMock(MassActionInterface::class);
        $massAction->expects($this->once())
            ->method('getOptions')
            ->willReturn($options);
        $datagrid = $this->createMock(DatagridInterface::class);
        $notificationMessage = new NotificationMessage();
        $notificationMessage->setResolved(new TestEnumValue(
            NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_NO,
            'No'
        ));
        $results = $this->createMock(IterableResultInterface::class);
        $results->expects($this->once())
            ->method('rewind');
        $results->expects($this->exactly(2))
            ->method('valid')
            ->willReturnOnConsecutiveCalls(true, false);
        $results->expects($this->once())
            ->method('current')
            ->willReturn(new ResultRecord([$notificationMessage]));
        $repository = $this->createMock(EnumValueRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with(NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_YES)
            ->willReturn(new TestEnumValue('test', 'Test'));
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with(NotificationMessage::class)
            ->willReturn($em);
        $em->expects($this->once())
            ->method('flush');

        $args = new MassActionHandlerArgs($massAction, $datagrid, $results);
        $result = $this->handler->handle($args);
        $this->assertEquals('marello.notificationmessage.mass_actions.resolve.success', $result->getMessage());
    }
}
