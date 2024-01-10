<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\InventoryBundle\Entity\Repository\InventoryLevelLogRecordRepository")
 * @ORM\Table(name="marello_inventory_level_log")
 * @Oro\Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-list-alt"
 *          }
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class InventoryLevelLogRecord
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\InventoryLevel", inversedBy="inventoryLevelLogRecords")
     * @ORM\JoinColumn(name="inventory_level_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="marello.inventory.inventorylevel.entity_label"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var InventoryLevel
     */
    protected $inventoryLevel;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\InventoryItem",
     *     cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="inventory_item_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="marello.inventory.inventoryitem.entity_label"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var InventoryItem
     */
    protected $inventoryItem;

    /**
     * @ORM\Column(name="warehouse_name", type="string", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var string
     */
    protected $warehouseName;

    /**
     * @ORM\Column(name="inventory_alteration", type="integer")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var int
     */
    protected $inventoryAlteration;

    /**
     * @ORM\Column(name="allocated_inventory_alteration", type="integer")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var int
     */
    protected $allocatedInventoryAlteration;

    /**
     * @ORM\Column(name="change_trigger", type="string")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var string
     */
    protected $changeTrigger;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var User
     */
    protected $user = null;

    /**
     * Subject field could be filled by a listener.
     *
     * @see \Marello\Bundle\InventoryBundle\EventListener\Doctrine\StockLevelSubjectHydrationSubscriber
     *
     * @var mixed
     */
    protected $subject = null;

    /**
     * @ORM\Column(name="subject_type", type="string", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var string
     */
    protected $subjectType = null;

    /**
     * @ORM\Column(name="subject_id", type="integer", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var int
     */
    protected $subjectId = null;

    /**
     * @ORM\Column(name="inventory_batch", type="string", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var string
     */
    protected $inventoryBatch;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(type="datetime", name="updated_at")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          }
     *      }
     * )
     *
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * InventoryLevel constructor.
     *
     * @param InventoryLevel $inventoryLevel
     * @param int           $inventoryAlt
     * @param int           $allocatedInventoryAlt
     * @param string        $changeTrigger
     * @param User          $user
     * @param mixed|null    $subject
     */
    public function __construct(
        InventoryLevel $inventoryLevel,
        $inventoryAlt,
        $allocatedInventoryAlt,
        $changeTrigger,
        User $user = null,
        $subject = null,
        $inventoryBatch = null
    ) {
        $this->inventoryLevel               = $inventoryLevel;
        $this->inventoryAlteration          = $inventoryAlt;
        $this->allocatedInventoryAlteration = $allocatedInventoryAlt;
        $this->changeTrigger                = $changeTrigger;
        $this->user                         = $user;
        $this->subject                      = $subject;
        $this->inventoryItem                = $inventoryLevel->getInventoryItem();
        $this->warehouseName                = $inventoryLevel->getWarehouse()->getLabel();
        $this->inventoryBatch               = $inventoryBatch;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return InventoryLevel
     */
    public function getInventoryLevel(): InventoryLevel
    {
        return $this->inventoryLevel;
    }

    /**
     * @return int
     */
    public function getInventoryDiff(): int
    {
        return $this->inventoryAlteration;
    }

    /**
     * @return int
     */
    public function getAllocatedInventoryDiff(): int
    {
        return $this->allocatedInventoryAlteration;
    }

    /**
     * @return string
     */
    public function getChangeTrigger(): string
    {
        return $this->changeTrigger;
    }

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getSubjectType(): ?string
    {
        return $this->subjectType;
    }

    /**
     * @return int
     */
    public function getSubjectId(): ?int
    {
        return $this->subjectId;
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdateTimestamp(): void
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersistTimestamp(): void
    {
        $this->createdAt = $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @return InventoryItem
     */
    public function getInventoryItem(): InventoryItem
    {
        return $this->inventoryItem;
    }

    /**
     * @return string|null
     */
    public function getWarehouseName(): ?string
    {
        return $this->warehouseName;
    }

    /**
     * @param string $warehouseName
     * @return $this
     */
    public function setWarehouseName(string $warehouseName)
    {
        $this->warehouseName = $warehouseName;

        return $this;
    }

    /**
     * @return string
     */
    public function getInventoryBatch(): ?string
    {
        return $this->inventoryBatch;
    }

    /**
     * @param string $inventoryBatch
     */
    public function setInventoryBatch(string $inventoryBatch): self
    {
        $this->inventoryBatch = $inventoryBatch;

        return $this;
    }
}
