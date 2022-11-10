<?php

namespace Marello\Bundle\TaskBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures(['@MarelloTaskBundle/Tests/Functional/DataFixtures/task_data.yml']);
    }

    public function testTasksWidgetAction()
    {
        /** Return list of the task */
        $this->client->request(Request::METHOD_GET, $this->getUrl('marello_task_widget_sidebar_allocation_tasks'));
        $response = $this->client->getResponse();
        self::assertResponseStatusCodeEquals($response, Response::HTTP_OK);

        self::assertStringContainsString('Allocation task', $response->getContent());
        self::assertStringNotContainsString('Non-allocation task', $response->getContent());
    }
}
