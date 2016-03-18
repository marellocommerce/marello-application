<?php

namespace Marello\Bundle\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Model\EmailTemplateInterface;
use Oro\Bundle\NotificationBundle\Processor\EmailNotificationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="marello_notification")
 */
class Notification implements EmailNotificationInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\EmailBundle\Entity\EmailTemplate", cascade={})
     * @ORM\JoinColumn(nullable=false)
     *
     * @var EmailTemplate
     */
    protected $template;

    /**
     * @ORM\Column(type="json_array", nullable=false)
     *
     * @var array
     */
    protected $recipients;

    /**
     * Notification constructor.
     *
     * @param EmailTemplate $template
     * @param array         $recipients
     */
    public function __construct(EmailTemplate $template, array $recipients)
    {
        $this->template = $template;
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
