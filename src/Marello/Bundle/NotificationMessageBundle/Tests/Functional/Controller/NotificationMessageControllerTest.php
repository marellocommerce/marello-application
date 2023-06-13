<?php

namespace Marello\Bundle\NotificationMessageBundle\Tests\Functional\Controller;

use Marello\Bundle\NotificationMessageBundle\Tests\Functional\DataFixtures\LoadNotificationMessagesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class NotificationMessageControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadNotificationMessagesData::class,
        ]);
    }

    public function testIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_notificationmessage_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testView()
    {
        $notificationMessage = $this->getReference(LoadNotificationMessagesData::NOTIFICATION_MESSAGE_MESSAGE_1);
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_notificationmessage_view', ['id' => $notificationMessage->getId()])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertStringContainsString(LoadNotificationMessagesData::NOTIFICATION_MESSAGE_MESSAGE_1, $crawler->html());
    }
}
