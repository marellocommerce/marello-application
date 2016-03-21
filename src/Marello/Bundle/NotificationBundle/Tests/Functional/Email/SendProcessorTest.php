<?php

namespace Marello\Bundle\NotificationBundle\Tests\Functional\Email;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData;
use Marello\Bundle\NotificationBundle\Email\SendProcessor;
use Marello\Bundle\NotificationBundle\Entity\Notification;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\NotificationBundle\Processor\EmailNotificationProcessor;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @dbIsolation
 */
class SendProcessorTest extends WebTestCase
{
    /** @var ObjectProphecy */
    protected $oroProcessorMock;

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
        $order = $this->getReference('marello_order_1');

        $notificationsBefore = count(
            $this->getContainer()
                ->get('doctrine')
                ->getRepository(Notification::class)
                ->findAll()
        );

        $this->sendProcessor->sendNotification(
            'marello_order_accepted_confirmation',
            [$order->getBillingAddress()->getEmail()],
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
        $order = $this->getReference('marello_order_1');

        $this->sendProcessor->sendNotification(
            'this is not a valid template name',
            [$order->getBillingAddress()->getEmail()],
            $order
        );
    }
}
