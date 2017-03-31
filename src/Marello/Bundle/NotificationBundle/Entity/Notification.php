<?php

namespace Marello\Bundle\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\NotificationBundle\Model\ExtendNotification;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\NotificationBundle\Model\EmailNotificationInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="marello_notification")
 * @Oro\Config(
 *  defaultValues={
 *      "grouping"={
 *          "groups"={"activity"}
 *      },
 *      "ownership"={
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      },
 *  }
 * )
 */
class Notification extends ExtendNotification implements EmailNotificationInterface
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
     * @ORM\Column(type="text")
     *
     * @var string
     */
    protected $body;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * Notification constructor.
     *
     * @param EmailTemplate $template
     * @param array         $recipients
     * @param string        $body
     * @param Organization  $organization
     */
    public function __construct(EmailTemplate $template, array $recipients, $body, Organization $organization)
    {
        parent::__construct();

        $this->template     = $template;
        $this->recipients   = $recipients;
        $this->organization = $organization;
        $this->body         = $body;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Gets a template can be used to prepare a notification message
     *
     * @return EmailTemplate
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

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}
