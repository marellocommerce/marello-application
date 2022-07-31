<?php

namespace Marello\Bundle\TaskBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\TaskBundle\EventListener\DefaultTaskTypeListener;
use Marello\Bundle\TaskBundle\Tests\Unit\Stub\TaskStub;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;
use PHPUnit\Framework\TestCase;

class DefaultTaskTypeListenerTest extends TestCase
{
    /**
     * @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $registry;

    /**
     * @var DefaultTaskTypeListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->listener = new DefaultTaskTypeListener($this->registry);
    }

    public function testPrePersistWhenWithType()
    {
        $task = new TaskStub();
        $type = new TestEnumValue('test', 'test');
        $task->setType($type);

        $this->registry->expects($this->never())
            ->method('getManagerForClass');

        $this->listener->prePersist($task);
        $this->assertEquals($type, $task->getType());
    }

    public function testPrePersist()
    {
        $task = new TaskStub();
        $type = new TestEnumValue('test', 'test');

        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->expects($this->once())
            ->method('find')
            ->willReturn($type);
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($manager);

        $this->listener->prePersist($task);
        $this->assertEquals($type, $task->getType());
    }
}
