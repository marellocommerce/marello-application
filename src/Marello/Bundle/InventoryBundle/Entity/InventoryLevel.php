<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\InventoryBundle\Model\InventoryQtyAwareInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\InventoryBundle\Entity\Repository\InventoryLevelRepository")
 * @ORM\Table(name="marello_inventory_level",
 *       uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"inventory_item_id", "warehouse_id"})
 *      }
 * )
 * @Oro\Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-list-alt"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "ownership"={
 *              "owner_type"="ORGANIZATION",
 *              "owner_field_name"="organization",
 *              "owner_column_name"="organization_id"
 *          },
 *          "dataaudit"={
 *              "auditable"=false
 *          }
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class InventoryLevel implements OrganizationAwareInterface, InventoryQtyAwareInterface, ExtendEntityInterface
{
    use AuditableOrganizationAwareTrait;
    use ExtendEntityTrait;
    
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
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\InventoryItem", inversedBy="inventoryLevels")
     * @ORM\JoinColumn(name="inventory_item_id", referencedColumnName="id")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="marello.inventory.inventoryitem.entity_label"
     *          },
     *          "importexport"={
     *              "full"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=false
     *          }
     *      }
     * )
     *
     * @var InventoryItem
     */
    protected $inventoryItem;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\Warehouse")
     * @ORM\JoinColumn(name="warehouse_id", referencedColumnName="id", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=20,
     *              "full"=true,
     *          },
     *          "dataaudit"={
     *              "auditable"=false
     *          }
     *      }
     * )
     *
     * @var Warehouse
     */
    protected $warehouse;

    /**
     * @ORM\Column(name="inventory", type="integer")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "header"="Inventory Qty"
     *          },
     *          "dataaudit"={
     *              "auditable"=false
     *          }
     *      }
     * )
     *
     * @var int
     */
    protected $inventory = 0;

    /**
     * @ORM\Column(name="allocated_inventory", type="integer")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=false
     *          }
     *      }
     * )
     *
     * @var int
     */
    protected $allocatedInventory = 0;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=false
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
     *          },
     *          "dataaudit"={
     *              "auditable"=false
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    /**
     * @ORM\Column(name="managed_inventory", type="boolean", nullable=true, options={"default"=false})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "header"="Managed Inventory"
     *          },
     *          "dataaudit"={
     *              "auditable"=false
     *          }
     *      }
     * )
     *
     * @var boolean
     */
    protected $managedInventory;

    /**
     * @ORM\Column(name="pick_location", type="string", nullable=true, length=100)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "header"="Pick Location"
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var string
     */
    protected $pickLocation;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\InventoryBundle\Entity\InventoryBatch",
     *     mappedBy="inventoryLevel",
     *     cascade={"persist"},
     *     fetch="EAGER",
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"createdAt" = "DESC"})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=50,
     *              "full"=true
     *          },
     *      }
     * )
     *
     * @var InventoryBatch[]|Collection
     */
    protected $inventoryBatches;

    /**
     * @ORM\OneToMany(
     *     targetEntity="InventoryLevelLogRecord",
     *     mappedBy="inventoryLevel"
     * )
     *
     * @var Collection|InventoryLevelLogRecord[]
     */
    protected $inventoryLevelLogRecords;
    
    public function __construct()
    {
        $this->inventoryBatches = new ArrayCollection();
        $this->inventoryLevelLogRecords = new ArrayCollection();
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param InventoryItem $inventoryItem
     * @return $this
     */
    public function setInventoryItem(InventoryItem $inventoryItem)
    {
        $this->inventoryItem = $inventoryItem;

        return $this;
    }

    /**
     * @return InventoryItem
     */
    public function getInventoryItem()
    {
        return $this->inventoryItem;
    }

    /**
     * @param int $quantity
     * @return $this
     */
    public function setInventoryQty($quantity)
    {
        $this->inventory = $quantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getInventoryQty()
    {
        return $this->inventory;
    }

    /**
     * @param int $allocatedInventory
     * @return $this
     */
    public function setAllocatedInventoryQty($allocatedInventory)
    {
        $this->allocatedInventory = $allocatedInventory;

        return $this;
    }

    /**
     * @return int
     */
    public function getAllocatedInventoryQty()
    {
        return $this->allocatedInventory;
    }

    /**
     * @return int
     */
    public function getVirtualInventoryQty()
    {
        return $this->inventory - $this->allocatedInventory;
    }

    /**
     * @param Warehouse $warehouse
     * @return $this
     */
    public function setWarehouse(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * @return Warehouse
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdateTimestamp()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersistTimestamp()
    {
        $this->createdAt = $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return boolean
     */
    public function isManagedInventory()
    {
        return $this->managedInventory;
    }

    /**
     * @param mixed $managedInventory
     * @return $this
     */
    public function setManagedInventory($managedInventory): InventoryLevel
    {
        $this->managedInventory = $managedInventory;
        
        return $this;
    }

    /**
     * @param InventoryBatch $inventoryBatch
     * @return $this
     */
    public function addInventoryBatch(InventoryBatch $inventoryBatch)
    {
        if (!$this->inventoryBatches->contains($inventoryBatch)) {
            $inventoryBatch->setInventoryLevel($this);
            $this->inventoryBatches->add($inventoryBatch);
        }

        return $this;
    }

    /**
     * @param InventoryBatch $inventoryBatch
     * @return $this
     */
    public function removeInventoryBatch(InventoryBatch $inventoryBatch)
    {
        if ($this->inventoryBatches->contains($inventoryBatch)) {
            $this->inventoryBatches->removeElement($inventoryBatch);
        }

        return $this;
    }

    /**
     * @return ArrayCollection|InventoryBatch[]
     */
    public function getInventoryBatches()
    {
        return $this->inventoryBatches;
    }

    /**
     * @param InventoryLevelLogRecord $inventoryLevelLogRecord
     * @return $this
     */
    public function addInventoryLevelLogRecord(InventoryLevelLogRecord $inventoryLevelLogRecord)
    {
        if (!$this->inventoryLevelLogRecords->contains($inventoryLevelLogRecord)) {
            $this->inventoryLevelLogRecords->add($inventoryLevelLogRecord);
        }

        return $this;
    }

    /**
     * @param InventoryLevelLogRecord $inventoryLevelLogRecord
     * @return $this
     */
    public function removeInventoryLevelLogRecord(InventoryLevelLogRecord $inventoryLevelLogRecord)
    {
        if ($this->inventoryLevelLogRecords->contains($inventoryLevelLogRecord)) {
            $this->inventoryLevelLogRecords->removeElement($inventoryLevelLogRecord);
        }

        return $this;
    }

    /**
     * @return ArrayCollection|InventoryLevelLogRecord[]
     */
    public function getInventoryLevelLogRecords()
    {
        return $this->inventoryLevelLogRecords;
    }

    /**
     * {@inheritdoc}
     * @param $pickLocation
     * @return string
     */
    public function setPickLocation($pickLocation): InventoryLevel
    {
        $this->pickLocation = $pickLocation;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getPickLocation(): ?string
    {
        return $this->pickLocation;
    }
}
