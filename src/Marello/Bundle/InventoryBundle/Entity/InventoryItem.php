<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\ProductBundle\Model\ProductAwareInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\InventoryBundle\Entity\Repository\InventoryItemRepository")
 * @ORM\Table(
 *      name="marello_inventory_item",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"product_id"})
 *      }
 * )
 * @Oro\Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-cubes"
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
 *              "auditable"=true
 *          }
 *      }
 * )
 */
class InventoryItem implements ProductAwareInterface, OrganizationAwareInterface, ExtendEntityInterface
{
    use AuditableOrganizationAwareTrait;
    use ExtendEntityTrait;
    
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
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\InventoryBundle\Entity\InventoryLevel",
     *     mappedBy="inventoryItem",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"createdAt" = "DESC"})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var InventoryLevel[]|Collection
     */
    protected $inventoryLevels;

    /**
     * @ORM\OneToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\Product", inversedBy="inventoryItem")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=10,
     *              "identity"=true,
     *          }
     *      }
     * )
     *
     * @var ProductInterface
     */
    protected $product;

    /**
     * @ORM\Column(name="desired_inventory", type="integer", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
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
     * @ORM\Column(name="purchase_inventory", type="integer", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
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
     * @var string
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $replenishment;

    /**
     * @ORM\Column(name="backorder_allowed", type="boolean", nullable=true, options={"default"=false})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "header"="Backorder Allowed"
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var boolean
     */
    protected $backorderAllowed;

    /**
     * @ORM\Column(name="max_qty_to_backorder", type="integer", nullable=true, options={"default"=0})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "header"="Max Quantity To Backorder"
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var integer
     */
    protected $maxQtyToBackorder;

    /**
     * @ORM\Column(name="can_preorder", type="boolean", nullable=true, options={"default"=false})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "header"="Can Pre-order"
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var boolean
     */
    protected $canPreorder;

    /**
     * @ORM\Column(name="max_qty_to_preorder", type="integer", nullable=true, options={"default"=0})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "header"="Max Quantity To Pre-order"
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var integer
     */
    protected $maxQtyToPreorder;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="back_orders_datetime", type="datetime", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $backOrdersDatetime;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="pre_orders_datetime", type="datetime", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $preOrdersDatetime;

    /**
     * @ORM\Column(name="order_on_demand_allowed", type="boolean", nullable=true, options={"default"=false})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "header"="Order On Demand Allowed"
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var boolean
     */
    protected $orderOnDemandAllowed;

    /**
     * @ORM\Column(name="enable_batch_inventory", type="boolean", nullable=true, options={"default"=false})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "header"="Enable Batch Inventory"
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var boolean
     */
    protected $enableBatchInventory;

    /**
     * @var \Extend\Entity\EV_Marello_Product_Unit
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $productUnit;

    /**
     * InventoryItem constructor.
     *
     * @param ProductInterface|Product $product
     */
    public function __construct(ProductInterface $product)
    {
        $this->product = $product;
        $product->setInventoryItem($this);
        $this->inventoryLevels = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param ProductInterface $product
     *
     * @return $this
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return Product|ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
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
     * @return string
     */
    public function getReplenishment()
    {
        return $this->replenishment;
    }

    /**
     * @param string $replenishment
     *
     * @return $this
     */
    public function setReplenishment($replenishment)
    {
        $this->replenishment = $replenishment;

        return $this;
    }

    /**
     * @param InventoryLevel $inventoryLevel
     * @return $this
     */
    public function addInventoryLevel(InventoryLevel $inventoryLevel)
    {
        if (!$this->inventoryLevels->contains($inventoryLevel)) {
            $inventoryLevel->setInventoryItem($this);
            $this->inventoryLevels->add($inventoryLevel);
        }

        return $this;
    }

    /**
     * @param InventoryLevel $inventoryLevel
     * @return $this
     */
    public function removeInventoryLevel(InventoryLevel $inventoryLevel)
    {
        if ($this->inventoryLevels->contains($inventoryLevel)) {
            $this->inventoryLevels->removeElement($inventoryLevel);
        }

        return $this;
    }

    /**
     * @return ArrayCollection|InventoryLevel[]
     */
    public function getInventoryLevels()
    {
        return $this->inventoryLevels;
    }

    /**
     * @param Warehouse $warehouse
     * @return InventoryLevel|null
     */
    public function getInventoryLevel(Warehouse $warehouse)
    {
        $inventoryLevel = $this->getInventoryLevels()
            ->filter(function (InventoryLevel $inventoryLevel) use ($warehouse) {
                return $inventoryLevel->getWarehouse() === $warehouse;
            })
            ->first();
        if ($inventoryLevel) {
            return $inventoryLevel;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasInventoryLevels()
    {
        return ($this->inventoryLevels->count() > 0);
    }

    /**
     * @return boolean
     */
    public function isBackorderAllowed()
    {
        return $this->backorderAllowed;
    }

    /**
     * @param boolean $backorderAllowed
     * @return $this
     */
    public function setBackorderAllowed($backorderAllowed)
    {
        $this->backorderAllowed = $backorderAllowed;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxQtyToBackorder()
    {
        return $this->maxQtyToBackorder;
    }

    /**
     * @param int $maxQtyToBackorder
     * @return $this
     */
    public function setMaxQtyToBackorder($maxQtyToBackorder)
    {
        $this->maxQtyToBackorder = $maxQtyToBackorder;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCanPreorder()
    {
        return $this->canPreorder;
    }

    /**
     * @param bool $canPreorder
     * @return $this
     */
    public function setCanPreorder($canPreorder)
    {
        $this->canPreorder = $canPreorder;
        
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxQtyToPreorder()
    {
        return $this->maxQtyToPreorder;
    }

    /**
     * @param int $maxQtyToPreorder
     * @return $this
     */
    public function setMaxQtyToPreorder($maxQtyToPreorder)
    {
        $this->maxQtyToPreorder = $maxQtyToPreorder;

        return $this;
    }
    
    /**
     * @return \DateTime
     */
    public function getBackOrdersDatetime()
    {
        return $this->backOrdersDatetime;
    }

    /**
     * @param \DateTime|null $backOrdersDatetime
     * @return $this
     */
    public function setBackOrdersDatetime(\DateTime $backOrdersDatetime = null)
    {
        $this->backOrdersDatetime = $backOrdersDatetime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPreOrdersDatetime()
    {
        return $this->preOrdersDatetime;
    }

    /**
     * @param \DateTime|null $preOrdersDatetime
     * @return $this
     */
    public function setPreOrdersDatetime(\DateTime $preOrdersDatetime = null)
    {
        $this->preOrdersDatetime = $preOrdersDatetime;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOrderOnDemandAllowed()
    {
        return $this->orderOnDemandAllowed;
    }

    /**
     * @param mixed $orderOnDemandAllowed
     * @return InventoryItem
     */
    public function setOrderOnDemandAllowed($orderOnDemandAllowed)
    {
        $this->orderOnDemandAllowed = $orderOnDemandAllowed;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnableBatchInventory()
    {
        return $this->enableBatchInventory;
    }

    /**
     * @param mixed $enableBatchInventory
     * @return InventoryItem
     */
    public function setEnableBatchInventory($enableBatchInventory)
    {
        $this->enableBatchInventory = $enableBatchInventory;
        
        return $this;
    }

    /**
     * @return \Extend\Entity\EV_Marello_Product_Unit
     */
    public function getProductUnit()
    {
        return $this->productUnit;
    }

    /**
     * @param string $productUnit
     * @return $this
     */
    public function setProductUnit($productUnit)
    {
        $this->productUnit = $productUnit;

        return $this;
    }
}
