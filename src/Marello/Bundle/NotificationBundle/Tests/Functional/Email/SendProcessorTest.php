<?php

namespace Marello\Bundle\NotificationBundle\Tests\Functional\Email;

use Doctrine\ORM\NoResultException;

use Oro\Bundle\NotificationBundle\Async\Topic\SendEmailNotificationTopic;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\NotificationBundle\Email\SendProcessor;
use Marello\Bundle\NotificationBundle\Entity\Notification;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;

class SendProcessorTest extends WebTestCase
{
    use MessageQueueExtension;

    /** @var SendProcessor */
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
     * @covers SendProcessor::sendNotification
     */
    public function testSendsNotification()
    {
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

    /**
     * test if the message is sent to the consumer with the subject and content rendered instead of plain text
     * without the dynamic attributes like `entity.orderNumber`
     */
    public function testMessageSendIsRenderedTemplateAndSubject()
    {
        /** @var Order $order */
        $order = $this->getReference('marello_order_0');
        $this->sendProcessor->sendNotification(
            'marello_order_accepted_confirmation',
            [$order->getCustomer()],
            $order
        );

        self::assertMessageSent(SendEmailNotificationTopic::getName());
        $message = self::getSentMessage(SendEmailNotificationTopic::getName());
        // check that the subject and body have been rendered
        self::assertStringNotContainsString('{{ entity', $message['subject']);
        self::assertStringNotContainsString('{{ entity', $message['body']);
        self::assertEquals('text/html', $message['contentType']);
        self::assertStringContainsString($order->getOrderNumber(), $message['subject']);
        self::assertStringContainsString($order->getOrderNumber(), $message['body']);
    }


    /**
     * @covers SendProcessor::sendNotification
     */
    public function testSendsNotificationButDontSaveInDb()
    {
        /** @var Order $order */
        $order = $this->getReference('marello_order_0');
        $notificationsBefore = count(
            $this->getContainer()
                ->get('doctrine')
                ->getRepository(Notification::class)
                ->findAll()
        );

        static::assertEquals(0, $notificationsBefore);

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

        static::assertEquals($notificationsBefore, $notificationsAfter);
        self::assertMessageSent(SendEmailNotificationTopic::getName());
        $message = self::getSentMessage(SendEmailNotificationTopic::getName());
        // check that the subject and body have been rendered
        self::assertStringNotContainsString('{{ entity', $message['subject']);
        self::assertStringNotContainsString('{{ entity', $message['body']);
        self::assertEquals('text/html', $message['contentType']);
        self::assertStringContainsString($order->getOrderNumber(), $message['subject']);
        self::assertStringContainsString($order->getOrderNumber(), $message['body']);
    }
}
