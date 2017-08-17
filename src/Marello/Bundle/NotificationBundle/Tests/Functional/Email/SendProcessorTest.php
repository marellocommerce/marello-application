<?php

namespace Marello\Bundle\NotificationBundle\Tests\Functional\Email;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\NotificationBundle\Email\SendProcessor;
use Marello\Bundle\NotificationBundle\Entity\Notification;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;

class SendProcessorTest extends WebTestCase
{
    /** @var SendProcessor */
    protected $sendProcessor;

    public function setUp()
    {
        $this->initClient();

        $this->loadFixtures(
            [
                LoadOrderData::class,
            ]
        );

        $this->sendProcessor = $this->getContainer()->get('marello_notification.email.send_processor');
    }

    /**
     * @test
     * @covers SendProcessor::sendNotification
     */
    public function sendsNotifications()
    {
        /** @var Order $order */
        $order = $this->getReference('order0');

        $notificationsBefore = count(
            $this->getContainer()
                ->get('doctrine')
                ->getRepository(Notification::class)
                ->findAll()
        );

        $this->sendProcessor->sendNotification(
            'marello_order_accepted_confirmation',
            [$order->getCustomer()->getEmail()],
            $order
        );

        $notificationsAfter = count(
            $this->getContainer()
                ->get('doctrine')
                ->getRepository(Notification::class)
                ->findAll()
        );

        $this->assertEquals(1, $notificationsAfter - $notificationsBefore);
    }

    /**
     * @test
     * @covers SendProcessor::sendNotification
     * @expectedException \Marello\Bundle\NotificationBundle\Exception\MarelloNotificationException
     */
    public function throwsExceptionWhenTemplateIsNotFound()
    {
        /** @var Order $order */
        $order = $this->getReference('order1');

        $this->sendProcessor->sendNotification(
            'this is not a valid template name',
            [$order->getCustomer()->getEmail()],
            $order
        );
    }
}
