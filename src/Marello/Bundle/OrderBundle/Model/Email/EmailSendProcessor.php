<?php

namespace Marello\Bundle\OrderBundle\Model\Email;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\NotificationBundle\Processor\EmailNotificationProcessor;

class EmailSendProcessor
{
    /** @var EmailNotificationProcessor */
    protected $emailNotificationProcessor;

    /** @var ObjectManager */
    protected $manager;

    /**
     * EmailSendProcessor constructor.
     *
     * @param EmailNotificationProcessor $emailNotificationProcessor
     * @param ObjectManager              $manager
     */
    public function __construct(EmailNotificationProcessor $emailNotificationProcessor, ObjectManager $manager)
    {
        $this->emailNotificationProcessor = $emailNotificationProcessor;
        $this->manager                    = $manager;
    }

    /**
     * @param Order  $order
     * @param string $templateName
     */
    public function sendMessage(Order $order, $templateName)
    {
        $template = $this->manager->getRepository('OroEmailBundle:EmailTemplate')
            ->findOneBy(['name' => $templateName, 'entityName' => Order::class]);

        $notification = new OrderEmailNotification($template, [$order->getBillingAddress()->getEmail()]);

        $this->emailNotificationProcessor->process($order, [$notification]);
    }
}
