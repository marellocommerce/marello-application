<?php

namespace Marello\Bundle\NotificationBundle\Tests\Functional\Email;

use Doctrine\ORM\NoResultException;

use Oro\Bundle\NotificationBundle\Async\Topic\SendEmailNotificationTopic;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\NotificationBundle\Provider\EmailSendProcessor;
use Marello\Bundle\NotificationBundle\Entity\Notification;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;

class SendProcessorTest extends WebTestCase
{
    use MessageQueueExtension;

    /** @var EmailSendProcessor */
    protected $sendProcessor;

    public function setUp(): void
    {
        $this->initClient($this->generateBasicAuthHeader());

        $this->loadFixtures(
            [
                LoadOrderData::class,
            ]
        );

        $this->sendProcessor = $this->getContainer()->get('marello_notification.email.send_processor');
    }

    /**
     * @covers EmailSendProcessor::sendNotification
     */
    public function testSendsNotification()
    {
        $this->markTestSkipped(
            'Skipped due to "A new entity was found through the relationship
             "Oro\Bundle\EmailBundle\Entity\EmailUser#organization" that was not configured
              to cascade persist operations for entity: Oro." error.'
        );
        /** @var Order $order */
        $order = $this->getReference('marello_order_0');
        $notificationsBefore = count(
            $this->getContainer()
                ->get('doctrine')
                ->getRepository(Notification::class)
                ->findAll()
        );

        $this->sendProcessor->sendNotification(
            'marello_order_accepted_confirmation',
            [$order->getCustomer()],
            $order
        );

        $notificationsAfter = count(
            $this->getContainer()
                ->get('doctrine')
                ->getRepository(Notification::class)
                ->findAll()
        );

        $this->assertEquals(0, $notificationsAfter - $notificationsBefore);
    }

    /**
     * @throws NoResultException
     * @throws \Oro\Bundle\NotificationBundle\Exception\NotificationSendException
     */
    public function testExceptionIsThrownWhenTemplateIsNotFoundForEntity()
    {
        /** @var Order $order */
        $order = $this->getReference('marello_order_0');

        $this->expectException(NoResultException::class);

        $this->sendProcessor->sendNotification(
            'no_valid_template',
            [$order->getCustomer()],
            $order
        );

        self::assertMessagesEmpty(SendEmailNotificationTopic::getName());
    }
}
