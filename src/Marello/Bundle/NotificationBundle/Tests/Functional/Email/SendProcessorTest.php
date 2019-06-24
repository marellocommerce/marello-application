<?php

namespace Marello\Bundle\NotificationBundle\Tests\Functional\Email;

use Marello\Bundle\NotificationBundle\Exception\MarelloNotificationException;
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
        $this->initClient($this->generateBasicAuthHeader());

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

        $this->assertEquals(1, $notificationsAfter - $notificationsBefore);
    }

    /**
     * @test
     * @covers SendProcessor::sendNotification
     */
    public function isNotificationRendered()
    {
        /** @var Order $order */
        $order = $this->getReference('marello_order_0');

        $this->expectExceptionMessageRegExp(
            '/Email template with name .* for entity .* was not found. Check if such template exists./'
        );
        $this->expectException(MarelloNotificationException::class);


        $this->sendProcessor->sendNotification(
            'no_valid_template',
            [$order->getCustomer()],
            $order
        );
    }
}
