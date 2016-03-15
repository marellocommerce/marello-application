<?php

namespace Marello\Bundle\OrderBundle\Model\Email;

use Oro\Bundle\EmailBundle\Model\EmailTemplateInterface;
use Oro\Bundle\NotificationBundle\Processor\EmailNotificationInterface;

class OrderEmailNotification implements EmailNotificationInterface
{
    /** @var EmailTemplateInterface */
    protected $template;

    /** @var array */
    protected $recipients = [];

    /**
     * OrderEmailNotification constructor.
     *
     * @param EmailTemplateInterface $template
     * @param array                  $recipients
     */
    public function __construct($template, $recipients)
    {
        $this->template   = $template;
        $this->recipients = $recipients;
    }


    /**
     * Gets a template can be used to prepare a notification message
     *
     * @return EmailTemplateInterface
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Gets a list of email addresses can be used to send a notification message
     *
     * @return string[]
     */
    public function getRecipientEmails()
    {
        return $this->recipients;
    }
}
