<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *      name="marello_inventory_item",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"product_id", "warehouse_id"})
 *      }
 * )
 * @Config(
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
    const MODIFY_OPERATOR_INCREASE = 'increase';
    const MODIFY_OPERATOR_DECREASE = 'decrease';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\Product", inversedBy="inventoryItems")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=10,
     *              "header"="",
     *              "full"=true,
     *          }
     *      }
     * )
     * @var Product
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\Warehouse")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     * @var Warehouse
     */
    protected $warehouse;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=20,
     *              "header"="Total Stock"
     *          }
     *      }
     * )
     * @var int
     */
    protected $quantity = 0;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\InventoryBundle\Entity\InventoryLog",
     *     cascade={"persist", "remove"},
     *     mappedBy="inventoryItem",
     *     fetch="EXTRA_LAZY"
     * )
     *
     * @var Collection
     */
    protected $inventoryLogs;

    /**
     * InventoryItem constructor.
     */
    public function __construct()
    {
        $this->inventoryLogs = new ArrayCollection();
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
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     *
     * @return $this
     */
    public function setProduct($product = null)
    {
        $this->product = $product;

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
     * @param Warehouse $warehouse
     *
     * @return $this
     */
    public function setWarehouse(Warehouse $warehouse = null)
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @param int $amount
     *
     * @return $this
     */
    public function modifyQuantity($amount)
    {
        $this->quantity += $amount;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getInventoryLogs()
    {
        return $this->inventoryLogs;
    }

    /**
     * @param InventoryLog $log
     *
     * @return $this
     */
    public function addInventoryLog(InventoryLog $log)
    {
        $this->inventoryLogs->add($log);

        return $this;
    }

    /**
     * @param InventoryLog $log
     *
     * @return $this
     */
    public function removeInventoryLog(InventoryLog $log)
    {
        $this->inventoryLogs->removeElement($log);

        return $this;
    }
}
