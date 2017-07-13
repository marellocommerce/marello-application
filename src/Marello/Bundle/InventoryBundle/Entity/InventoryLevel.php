<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\UserBundle\Entity\User;

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
     *              "excluded"=true
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
     *              "header"="Level"
     *          }
     *      }
     * )
     *
     * @var int
     */
    protected $inventory;

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
    protected $allocatedInventory;

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
     * InventoryLevel constructor.
     *
     * @param InventoryItem $inventoryItem
     * @param int           $inventory
     * @param int           $allocatedInventory
     */
    public function __construct(
        InventoryItem $inventoryItem,
        $inventory,
        $allocatedInventory
    ) {
        $this->inventoryItem                = $inventoryItem;
        $this->inventory                    = $inventory;
        $this->allocatedInventory           = $allocatedInventory;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return InventoryItem
     */
    public function getInventoryItem()
    {
        return $this->inventoryItem;
    }

    /**
     * @return int
     */
    public function getStock()
    {
        return $this->inventory;
    }

    /**
     * @return int
     */
    public function getAllocatedStock()
    {
        return $this->allocatedInventory;
    }

    /**
     * @return int
     */
    public function getVirtualStock()
    {
        return $this->inventory - $this->allocatedInventory;
    }

    /**
     * @param Warehouse $warehouse
     * @return void
     */
    public function setWarehouse(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;
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
