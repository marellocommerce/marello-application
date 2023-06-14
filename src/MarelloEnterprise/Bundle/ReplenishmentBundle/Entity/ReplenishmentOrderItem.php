<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\ProductBundle\Model\ProductAwareInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="marello_repl_order_item")
 * @ORM\HasLifecycleCallbacks
 * @Oro\Config(
 *      defaultValues={
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "ownership"={
 *              "owner_type"="ORGANIZATION",
 *              "owner_field_name"="organization",
 *              "owner_column_name"="organization_id"
 *          }
 *      }
 * )
 */
class ReplenishmentOrderItem implements ProductAwareInterface, OrganizationAwareInterface
{
    use AuditableOrganizationAwareTrait;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ReplenishmentOrder", inversedBy="replOrderItems")
     * @ORM\JoinColumn(name="repl_order_id", nullable=false, onDelete="CASCADE")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var ReplenishmentOrder
     */
    protected $order;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\Product")
     * @ORM\JoinColumn(name="product_id", nullable=true, onDelete="SET NULL")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var Product
     */
    protected $product;

    /**
     * @ORM\Column(name="product_name", type="string")
     *
     * @var string
     */
    protected $productName;

    /**
     * @ORM\Column(name="product_sku", type="string")
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
    protected $productSku;

    /**
     * @ORM\Column(name="note", type="text", nullable=true)
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
    protected $note;

    /**
     * @ORM\Column(name="inventory_qty", type="integer", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var int
     */
    protected $inventoryQty;

    /**
     * @ORM\Column(name="total_inventory_qty", type="integer", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var int
     */
    protected $totalInventoryQty;

    /**
     * @ORM\Column(name="all_quantity", type="boolean")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var boolean
     */
    protected $allQuantity = false;

    /**
     * @ORM\Column(name="inventory_batches", type="json_array", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var array
     */
    protected $inventoryBatches = [];

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ReplenishmentOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param ReplenishmentOrder $order
     * @return ReplenishmentOrderItem
     */
    public function setOrder(ReplenishmentOrder $order)
    {
        $this->order = $order;

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
     * {@inheritdoc}
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;
        $this->productName = $product->getName();
        $this->productSku = $product->getSku();

        return $this;
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * @param string $productName
     * @return ReplenishmentOrderItem
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * @return string
     */
    public function getProductSku()
    {
        return $this->productSku;
    }

    /**
     * @param string $productSku
     * @return ReplenishmentOrderItem
     */
    public function setProductSku($productSku)
    {
        $this->productSku = $productSku;

        return $this;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param string $note
     * @return ReplenishmentOrderItem
     */
    public function setNote($note)
    {
        $this->note = $note;
        
        return $this;
    }

    /**
     * @return int
     */
    public function getInventoryQty()
    {
        return $this->inventoryQty;
    }

    /**
     * @param int $inventoryQty
     * @return ReplenishmentOrderItem
     */
    public function setInventoryQty($inventoryQty)
    {
        $this->inventoryQty = $inventoryQty;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalInventoryQty()
    {
        return $this->totalInventoryQty;
    }

    /**
     * @param int $totalInventoryQty
     * @return ReplenishmentOrderItem
     */
    public function setTotalInventoryQty($totalInventoryQty)
    {
        $this->totalInventoryQty = $totalInventoryQty;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllQuantity(): bool
    {
        return $this->allQuantity;
    }

    /**
     * @param bool $allQuantity
     * @return self
     */
    public function setAllQuantity(bool $allQuantity): self
    {
        $this->allQuantity = $allQuantity;

        return $this;
    }

    /**
     * @return array
     */
    public function getInventoryBatches()
    {
        return $this->inventoryBatches ? : [];
    }

    /**
     * @param array $batches
     * @return $this
     */
    public function setInventoryBatches(array $batches)
    {
        $this->inventoryBatches = $batches;

        return $this;
    }
}
