<?php

namespace Marello\Bundle\NotificationAlertBundle\Tests\Functional\Controller;

use Marello\Bundle\NotificationAlertBundle\Tests\Functional\DataFixtures\LoadNotificationAlertsData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class NotificationAlertControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadNotificationAlertsData::class,
        ]);
    }

    public function testIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_notificationalert_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testView()
    {
        $notificationAlert = $this->getReference(LoadNotificationAlertsData::NOTIFICATION_ALERT_MESSAGE_1);
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_notificationalert_view', ['id' => $notificationAlert->getId()])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertStringContainsString(LoadNotificationAlertsData::NOTIFICATION_ALERT_MESSAGE_1, $crawler->html());
    }

    public function testInfo()
    {
        $notificationAlert = $this->getReference(LoadNotificationAlertsData::NOTIFICATION_ALERT_MESSAGE_1);
        $this->client->request(
            'GET',
            $this->getUrl('marello_notificationalert_widget_info', ['id' => $notificationAlert->getId()])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_OK);
        $this->assertEquals($notificationAlert->getId(), $result['entity']->getId());
    }
}
