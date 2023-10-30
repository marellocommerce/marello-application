<?php

namespace Marello\Bundle\NotificationMessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Oro\Bundle\ActivityBundle\Model\ActivityInterface;
use Oro\Bundle\ActivityBundle\Model\ExtendActivity;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;
use Oro\Bundle\UserBundle\Entity\Group;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\NotificationMessageBundle\Entity\Repository\NotificationMessageRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="marello_notification_message")
 * @Oro\Config(
 *     routeView="marello_notificationmessage_view",
 *     routeName="marello_notificationmessage_index",
 *     defaultValues={
 *         "grouping"={
 *             "groups"={"activity"}
 *         },
 *         "ownership"={
 *             "owner_type"="ORGANIZATION",
 *             "owner_field_name"="organization",
 *             "owner_column_name"="organization_id"
 *         },
 *         "security"={
 *             "type"="ACL",
 *             "group_name"=""
 *         },
 *     }
 * )
 */
class NotificationMessage implements OrganizationAwareInterface, ActivityInterface, ExtendEntityInterface
{
    use AuditableOrganizationAwareTrait;
    use EntityCreatedUpdatedAtTrait;
    use ExtendActivity;
    use ExtendEntityTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=32)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=255)
     */
    private $message;

    /**
     * @var int|null
     *
     * @ORM\Column(name="related_item_id", type="integer", nullable=true)
     */
    private $relatedItemId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="related_item_class", type="string", length=100, nullable=true)
     */
    private $relatedItemClass;

    /**
     * @var string
     *
     * @ORM\Column(name="solution", type="text", nullable=true)
     */
    private $solution;

    /**
     * @var string|null
     *
     * @ORM\Column(name="operation", type="string", length=100, nullable=true)
     */
    private $operation;

    /**
     * @var string|null
     *
     * @ORM\Column(name="step", type="string", length=100, nullable=true)
     */
    private $step;

    /**
     * @var string|null
     *
     * @ORM\Column(name="external_id", type="string", length=100, nullable=true)
     */
    private $externalId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="log", type="text", nullable=true)
     */
    private $log;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\Group")
     * @ORM\JoinColumn(name="user_group_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     *
     * @var Group|null
     */
    protected $userGroup;

    /**
     * @var \Extend\Entity\EV_Marello_NotificationMessage_AlertType
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $alertType;

    /**
     * @var \Extend\Entity\EV_Marello_NotificationMessage_Source
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $source;

    /**
     * @var \Extend\Entity\EV_Marello_NotificationMessage_Resolved
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $resolved;

    /**
     * @var int
     *
     * @ORM\Column(name="count", type="integer")
     */
    private $count = 1;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return self
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return \Extend\Entity\EV_Marello_NotificationMessage_AlertType
     */
    public function getAlertType(): AbstractEnumValue
    {
        return $this->alertType;
    }

    /**
     * @param \Extend\Entity\EV_Marello_NotificationMessage_AlertType|AbstractEnumValue $alertType
     * @return self
     */
    public function setAlertType($alertType): self
    {
        $this->alertType = $alertType;

        return $this;
    }

    /**
     * @return \Extend\Entity\EV_Marello_NotificationMessage_Source
     */
    public function getSource(): AbstractEnumValue
    {
        return $this->source;
    }

    /**
     * @param \Extend\Entity\EV_Marello_NotificationMessage_Source|AbstractEnumValue $source
     * @return self
     */
    public function setSource($source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return \Extend\Entity\EV_Marello_NotificationMessage_Resolved
     */
    public function getResolved(): AbstractEnumValue
    {
        return $this->resolved;
    }

    /**
     * @param \Extend\Entity\EV_Marello_NotificationMessage_Resolved|AbstractEnumValue $resolved
     * @return self
     */
    public function setResolved($resolved): self
    {
        $this->resolved = $resolved;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRelatedItemId(): ?int
    {
        return $this->relatedItemId;
    }

    /**
     * @param int|null $relatedItemId
     * @return self
     */
    public function setRelatedItemId(?int $relatedItemId = null): self
    {
        $this->relatedItemId = $relatedItemId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRelatedItemClass(): ?string
    {
        return $this->relatedItemClass;
    }

    /**
     * @param string|null $relatedItemClass
     * @return self
     */
    public function setRelatedItemClass(?string $relatedItemClass = null): self
    {
        $this->relatedItemClass = $relatedItemClass;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSolution(): ?string
    {
        return $this->solution;
    }

    /**
     * @param string|null $solution
     * @return self
     */
    public function setSolution(?string $solution = null): self
    {
        $this->solution = $solution;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOperation(): ?string
    {
        return $this->operation;
    }

    /**
     * @param string|null $operation
     * @return self
     */
    public function setOperation(?string $operation = null): self
    {
        $this->operation = $operation;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStep(): ?string
    {
        return $this->step;
    }

    /**
     * @param string|null $step
     * @return self
     */
    public function setStep(?string $step = null): self
    {
        $this->step = $step;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /**
     * @param string|null $externalId
     * @return self
     */
    public function setExternalId(?string $externalId = null): self
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLog(): ?string
    {
        return $this->log;
    }

    /**
     * @param string|null $log
     * @return self
     */
    public function setLog(?string $log = null): self
    {
        $this->log = $log;

        return $this;
    }

    /**
     * @return Group|null
     */
    public function getUserGroup(): ?Group
    {
        return $this->userGroup;
    }

    /**
     * @param Group|null $userGroup
     * @return self
     */
    public function setUserGroup(?Group $userGroup = null): self
    {
        $this->userGroup = $userGroup;

        return $this;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @return self
     */
    public function increaseCount(): self
    {
        $this->count++;

        return $this;
    }
}
