<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\InventoryBundle\Entity\Repository\InventoryItemRepository")
 * @ORM\Table(
 *      name="marello_inventory_item",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"product_id", "warehouse_id"})
 *      }
 * )
 * @Oro\Config(
 *      defaultValues={
 *          "security"={
 *              "type"="ACL",
 *              "permissions"="VIEW;EDIT",
 *              "group_name"=""
 *          }
 *      }
 * )
 */
class InventoryItem
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
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\Product", inversedBy="inventoryItems")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=10,
     *              "full"=true,
     *          }
     *      }
     * )
     *
     * @var Product
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\Warehouse")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var Warehouse
     */
    protected $warehouse;

    /**
     * @ORM\OneToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\StockLevel", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     *
     * @var StockLevel
     */
    protected $currentLevel = null;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\InventoryBundle\Entity\StockLevel",
     *     mappedBy="inventoryItem",
     *     cascade={"persist", "remove"}
     * )
     *
     * @var StockLevel[]|Collection
     */
    protected $levels;

    /**
     * InventoryItem constructor.
     *
     * @param Product   $product
     * @param Warehouse $warehouse
     */
    public function __construct(Product $product, Warehouse $warehouse)
    {
        $this->product   = $product;
        $this->warehouse = $warehouse;
        $this->levels    = new ArrayCollection();
    }

    /**
     * @param Product   $product
     * @param Warehouse $warehouse
     * @param int   $stock
     * @param           $allocatedStock
     * @param           $trigger
     *
     * @return InventoryItem
     */
    public static function withStockLevel(
        Product $product,
        Warehouse $warehouse,
        $stock,
        $allocatedStock,
        $trigger
    ) {
        $inventoryItem = new self($product, $warehouse);
        new StockLevel($inventoryItem, $stock, $allocatedStock, $trigger);

        return $inventoryItem;
    }

    /**
     * @param StockLevel $newLevel
     *
     * @return $this
     */
    public function changeCurrentLevel(StockLevel $newLevel)
    {
        $this->levels->add($newLevel);
        $this->currentLevel = $newLevel;

        return $this;
    }

    /**
     * @return StockLevel
     */
    public function getCurrentLevel()
    {
        return $this->currentLevel;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return Warehouse
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }
}
