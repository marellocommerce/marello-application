<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Entity()
 * @ORM\Table(name="marello_inventory_level",
 *       uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"inventory_item_id", "warehouse_id"})
 *      }
 * )
 * @Oro\Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-list-alt"
 *          }
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class InventoryLevel
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
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\InventoryItem", inversedBy="levels")
     * @ORM\JoinColumn(name="inventory_item_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="marello.inventory.inventoryitem.entity_label"
     *          },
     *          "importexport"={
     *              "full"=true
     *          }
     *      }
     * )
     *
     * @var InventoryItem
     */
    protected $inventoryItem;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\Warehouse")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=20,
     *              "full"=true,
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
     *          }
     *      }
     * )
     *
     * @var int
     */
    protected $allocatedInventory = 0;

    /**
     * @ORM\Column(name="desired_inventory", type="integer")
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
    protected $desiredInventory = 0;

    /**
     * @ORM\Column(name="purchase_inventory", type="integer")
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
    protected $purchaseInventory = 0;

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
     */
    protected $updatedAt;

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
     * @return int
     */
    public function getDesiredInventory()
    {
        return $this->desiredInventory;
    }

    /**
     * @param int $desiredInventory
     * @return $this
     */
    public function setDesiredInventory($desiredInventory)
    {
        $this->desiredInventory = $desiredInventory;

        return $this;
    }

    /**
     * @return int
     */
    public function getPurchaseInventory()
    {
        return $this->purchaseInventory;
    }

    /**
     * @param int $purchaseInventory
     * @return $this
     */
    public function setPurchaseInventory($purchaseInventory)
    {
        $this->purchaseInventory = $purchaseInventory;

        return $this;
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
}
