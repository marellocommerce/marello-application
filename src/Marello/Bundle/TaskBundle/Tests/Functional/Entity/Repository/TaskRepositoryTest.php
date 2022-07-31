<?php

namespace Marello\Bundle\TaskBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\TaskBundle\Entity\Repository\TaskRepository;
use Oro\Bundle\TaskBundle\Entity\Task;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\UserBundle\Tests\Functional\DataFixtures\LoadUserData;

class TaskRepositoryTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures(['@MarelloTaskBundle/Tests/Functional/DataFixtures/task_data.yml']);
    }

    private function getTaskRepository(): TaskRepository
    {
        return self::getContainer()->get('doctrine')->getRepository(Task::class);
    }

    public function testGetTasksAssignedTo()
    {
        $taskOwner = $this->getReference(LoadUser::USER);
        $result = $this->getTaskRepository()->getTasksAssignedTo($taskOwner, 10);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Non-allocation task', reset($result));
    }

    public function testGetAllocationTasksAssignedTo()
    {
        $taskOwner = $this->getReference(LoadUser::USER);
        $result = $this->getTaskRepository()->getAllocationTasksAssignedTo($taskOwner, 10);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Allocation task', reset($result));
    }
}
