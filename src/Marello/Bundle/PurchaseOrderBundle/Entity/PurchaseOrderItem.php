<?php

namespace Marello\Bundle\PurchaseOrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\ProductBundle\Model\ProductAwareInterface;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\PurchaseOrderBundle\Entity\Repository\PurchaseOrderItemRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="marello_purchase_order_item")
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
class PurchaseOrderItem implements
    ProductAwareInterface,
    OrganizationAwareInterface
{
    use EntityCreatedUpdatedAtTrait;
    use AuditableOrganizationAwareTrait;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETE = 'complete';
    public const STATUS_CLOSED = 'closed';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @var ProductInterface
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\Product")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $product;

    /**
     * @var PurchaseOrder
     *
     * @ORM\ManyToOne(targetEntity="PurchaseOrder", inversedBy="items")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(name="product_sku", type="string")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $productSku;

    /**
     * @var string
     *
     * @ORM\Column(name="product_name", type="string")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $productName;

    /**
     * @var string
     *
     * @ORM\Column(name="supplier", type="string")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $supplier;

    /**
     * @var int
     *
     * @ORM\Column(name="ordered_amount", type="integer")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $orderedAmount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="received_amount", type="integer")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $receivedAmount = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="purchase_price_value", type="money")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $purchasePriceValue;

    /**
     * @var ProductPrice
     */
    protected $purchasePrice;

    /**
     * @var float
     *
     * @ORM\Column(name="row_total", type="money", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $rowTotal;
    
    /**
     * @var array $data
     *
     * @ORM\Column(name="data", type="json_array", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "email"={
     *              "available_in_template"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $data = [];

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $status = self::STATUS_DRAFT;

    /**
     * PurchaseOrderItem constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('#%s', $this->productName);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @Assert\Callback
     *
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (($this->receivedAmount < 0) || ($this->receivedAmount > $this->orderedAmount)) {
            $context
                ->buildViolation('marello.purchaseorder.purchaseorderitem.messages.error.received_amount')
                ->atPath('receivedAmount')
                ->addViolation();
        }
    }

    /**
     * @param PurchaseOrder $order
     *
     * @return $this
     */
    public function setOrder(PurchaseOrder $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return PurchaseOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param ProductInterface|Product $product
     *
     * @return $this
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;
        $this->productName = $this->product->getDenormalizedDefaultName();
        $this->productSku = $this->product->getSku();

        return $this;
    }

    /**
     * @return ProductInterface|Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param int $orderedAmount
     *
     * @return $this
     */
    public function setOrderedAmount($orderedAmount)
    {
        $this->orderedAmount = $orderedAmount;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderedAmount()
    {
        return $this->orderedAmount;
    }
    
    /**
     * @param string $productSku
     *
     * @return $this
     */
    public function setProductSku($productSku)
    {
        $this->productSku = $productSku;
        
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
     * @param string $productName
     *
     * @return $this
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;

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
     * @param String $supplier
     *
     * @return $this
     */
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * @return string
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @return int
     */
    public function getReceivedAmount()
    {
        return $this->receivedAmount;
    }

    /**
     * @return ProductPrice
     */
    public function getPurchasePrice()
    {
        return $this->purchasePrice;
    }

    /**
     * @param ProductPrice $purchasePrice
     * @return $this
     */
    public function setPurchasePrice(ProductPrice $purchasePrice = null)
    {
        $this->purchasePrice = $purchasePrice;
        $this->updatePurchasePrice();

        return $this;
    }

    /**
     * @ORM\PostLoad
     */
    public function loadPurchasePrice()
    {
        $price = new ProductPrice();
        $price
            ->setCurrency($this->order->getSupplier()->getCurrency())
            ->setValue($this->purchasePriceValue);

        if ($this->product) {
            $price->setProduct($this->product);
        }

        $this->purchasePrice = $price;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatePurchasePrice()
    {
        if ($this->purchasePrice) {
            $this->purchasePriceValue = $this->purchasePrice->getValue();
        } else {
            $this->purchasePriceValue = null;
        }
    }

    /**
     * @return float
     */
    public function getRowTotal()
    {
        return $this->rowTotal;
    }

    /**
     * @param float $rowTotal
     * @return $this
     */
    public function setRowTotal($rowTotal)
    {
        $this->rowTotal = $rowTotal;
        
        return $this;
    }
    
    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * @param int $receivedAmount
     * @return $this
     */
    public function setReceivedAmount($receivedAmount)
    {
        $this->receivedAmount = $receivedAmount;

        return $this;
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function preSaveSetSupplier()
    {
        $this->setSupplier($this->order->getSupplier()->getName());
    }
}
