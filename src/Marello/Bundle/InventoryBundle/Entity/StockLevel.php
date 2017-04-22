<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\InventoryBundle\Entity\Repository\StockLevelRepository")
 * @ORM\Table(name="marello_inventory_level")
 * @Oro\Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-list-alt"
 *          }
 *      }
 * )
 */
class StockLevel
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
     * StockLevel constructor.
     *
     * @param InventoryItem $inventoryItem
     * @param int           $inventory
     * @param int           $inventoryAlt
     * @param int           $allocatedInventory
     * @param int           $allocatedInventoryAlt
     * @param string        $changeTrigger
     * @param User          $user
     * @param mixed|null    $subject
     */
    public function __construct(
        InventoryItem $inventoryItem,
        $inventory,
        $inventoryAlt,
        $allocatedInventory,
        $allocatedInventoryAlt,
        $changeTrigger,
        User $user = null,
        $subject = null
    ) {
        $this->inventoryItem                = $inventoryItem;
        $this->inventory                    = $inventory;
        $this->inventoryAlteration          = $inventoryAlt;
        $this->allocatedInventory           = $allocatedInventory;
        $this->allocatedInventoryAlteration = $allocatedInventoryAlt;
        $this->changeTrigger                = $changeTrigger;
        $this->user                         = $user;
        $this->subject                      = $subject;
        $this->createdAt                    = new \DateTime();
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
    public function getStockDiff()
    {
        return $this->inventoryAlteration;
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
    public function getAllocatedStockDiff()
    {
        return $this->allocatedInventoryAlteration;
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
     * @return int
     */
    public function getVirtualStockDiff()
    {
        return $this->getStockDiff() - $this->getAllocatedStockDiff();
    }

    /**
     * @return string
     */
    public function getChangeTrigger()
    {
        return $this->changeTrigger;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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
    public function getSubjectType()
    {
        return $this->subjectType;
    }

    /**
     * @return int
     */
    public function getSubjectId()
    {
        return $this->subjectId;
    }

    public function __toString()
    {
        return (string) $this->getAllocatedStock();
    }
}
