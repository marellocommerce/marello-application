<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\InventoryBundle\Entity\Repository\StockLevelRepository")
 * @ORM\Table(name="marello_inventory_stock_level")
 * @Oro\Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-list-alt"
 *          }
 *      }
 * )
 */
class StockLevel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
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
     * @ORM\Column(type="integer")
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
    protected $stock;

    /**
     * @ORM\Column(type="integer")
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
    protected $allocatedStock;

    /**
     * @ORM\Column(type="string")
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
     * @ORM\OneToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\StockLevel", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var StockLevel
     */
    protected $previousLevel = null;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true)
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
    protected $author = null;

    /**
     * Subject field is filled using a listener.
     *
     * @see Marello\Bundle\InventoryBundle\EventListener\Doctrine\StockLevelSubjectHydrationSubscriber
     *
     * @var mixed
     */
    protected $subject = null;

    /**
     * @ORM\Column(type="string", nullable=true)
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
     * @ORM\Column(type="integer", nullable=true)
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
     * @ORM\Column(type="datetime")
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
     * @param int           $stock
     * @param int           $allocatedStock
     * @param string        $changeTrigger
     * @param StockLevel    $previousLevel
     * @param User          $author
     * @param mixed|null    $subject
     */
    public function __construct(
        InventoryItem $inventoryItem,
        $stock,
        $allocatedStock,
        $changeTrigger,
        StockLevel $previousLevel = null,
        User $author = null,
        $subject = null
    ) {
        $this->inventoryItem  = $inventoryItem;
        $this->stock          = $stock;
        $this->allocatedStock = $allocatedStock;
        $this->changeTrigger  = $changeTrigger;
        $this->previousLevel  = $previousLevel;
        $this->author         = $author;
        $this->subject        = $subject;
        $this->createdAt      = new \DateTime();
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
        return $this->stock - ($this->previousLevel ? $this->previousLevel->getStock() : 0);
    }

    /**
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @return int
     */
    public function getAllocatedStockDiff()
    {
        return $this->allocatedStock - ($this->previousLevel ? $this->previousLevel->getAllocatedStock() : 0);
    }

    /**
     * @return int
     */
    public function getAllocatedStock()
    {
        return $this->allocatedStock;
    }

    /**
     * @return int
     */
    public function getVirtualStock()
    {
        return $this->stock - $this->allocatedStock;
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
     * @return StockLevel
     */
    public function getPreviousLevel()
    {
        return $this->previousLevel;
    }

    /**
     * @param StockLevel $previousLevel
     *
     * @return $this
     */
    public function setPreviousLevel($previousLevel)
    {
        $this->previousLevel = $previousLevel;

        return $this;
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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
        return (string) $this->previousLevel->getAllocatedStock();
    }
}
