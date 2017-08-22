<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

use Marello\Bundle\InventoryBundle\Model\ExtendInventoryItem;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;

/**
 * @ORM\Entity()
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
 *              "permissions"="VIEW;EDIT",
 *              "group_name"=""
 *          }
 *      }
 * )
 */
class InventoryItem extends ExtendInventoryItem implements ProductInventoryAwareInterface
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
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\InventoryBundle\Entity\InventoryLevel",
     *     mappedBy="inventoryItem",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"createdAt" = "DESC"})
     * @Oro\ConfigField(
     *      defaultValues={
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
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\Product", inversedBy="inventoryItems")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=10,
     *              "full"=true,
     *          }
     *      }
     * )
     *
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var string
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $replenishment;

    /**
     * @var Warehouse
     */
    protected $warehouse;

    /**
     * InventoryItem constructor.
     *
     * @param Warehouse $warehouse
     * @param ProductInterface   $product
     */
    public function __construct(Warehouse $warehouse, ProductInterface $product = null)
    {
        $this->product   = $product;
        $this->warehouse = $warehouse;
        $this->inventoryLevels    = new ArrayCollection();
    }

    /**
     * @return Warehouse
     */
    public function getWarehouse()
    {
        return $this->warehouse;
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
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
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
     * @return ArrayCollection
     */
    public function getInventoryLevels()
    {
        return $this->inventoryLevels;
    }

    /**
     * @return bool
     */
    public function hasInventoryLevels()
    {
        return ($this->inventoryLevels->count() > 0);
    }
}
