<?php

namespace Marello\Bundle\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\CurrencyBundle\Entity\PriceAwareInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\UserBundle\Entity\Ownership\AuditableUserAwareTrait;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;

use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Marello\Bundle\TaxBundle\Model\TaxAwareInterface;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\OrderBundle\Model\OrderItemTypeInterface;
use Marello\Bundle\OrderBundle\Model\QuantityAwareInterface;
use Marello\Bundle\ProductBundle\Model\ProductAwareInterface;
use Marello\Bundle\PricingBundle\Model\CurrencyAwareInterface;
/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\OrderBundle\Entity\Repository\OrderItemRepository")
 * @Oro\Config(
 *      defaultValues={
*           "security"={
*               "type"="ACL",
*               "group_name"=""
*           },
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "ownership"={
 *               "owner_type"="USER",
 *               "owner_field_name"="owner",
 *               "owner_column_name"="user_owner_id",
 *               "organization_field_name"="organization",
 *               "organization_column_name"="organization_id"
 *          },
 *      }
 * )
 * @ORM\Table(name="marello_order_order_item")
 * @ORM\HasLifecycleCallbacks()
 */
class OrderItem implements
    CurrencyAwareInterface,
    QuantityAwareInterface,
    PriceAwareInterface,
    TaxAwareInterface,
    ProductAwareInterface,
    OrderAwareInterface,
    OrganizationAwareInterface,
    ExtendEntityInterface
{
    use AuditableUserAwareTrait;
    use ExtendEntityTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var ProductInterface
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\Product")
     * @ORM\JoinColumn(onDelete="SET NULL")
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
     * @var string
     *
     * @ORM\Column(name="product_name",type="string", nullable=false)
     */
    protected $productName;

    /**
     * @var string
     *
     * @ORM\Column(name="product_sku",type="string", nullable=false)
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
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="items")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "full"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $order;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity",type="integer",nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $quantity;

    /**
     * @var int
     *
     * @ORM\Column(name="price",type="money")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $price;

    /**
     * @var int
     *
     * @ORM\Column(name="original_price_incl_tax",type="money", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $originalPriceInclTax;

    /**
     * @var int
     *
     * @ORM\Column(name="original_price_excl_tax",type="money", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $originalPriceExclTax;

    /**
     * @var int
     *
     * @ORM\Column(name="purchase_price_incl",type="money", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $purchasePriceIncl;

    /**
     * @var int
     *
     * @ORM\Column(name="tax",type="money")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $tax;

    /**
     * @var float
     *
     * @ORM\Column(name="tax_percent", type="percent", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $taxPercent;

    /**
     * @var float
     *
     * @ORM\Column(name="discount_percent", type="percent", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $discountPercent;

    /**
     * @var double
     *
     * @ORM\Column(name="discount_amount", type="money", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $discountAmount;

    /**
     * @var int
     *
     * @ORM\Column(name="row_total_incl_tax",type="money", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $rowTotalInclTax;

    /**
     * @var int
     *
     * @ORM\Column(name="row_total_excl_tax",type="money", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $rowTotalExclTax;
    
    /**
     * @var ReturnItem[]|Collection
     *
     * @ORM\OneToMany(targetEntity="Marello\Bundle\ReturnBundle\Entity\ReturnItem", mappedBy="orderItem", cascade={})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $returnItems;

    /**
     * @var TaxCode
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\TaxBundle\Entity\TaxCode")
     * @ORM\JoinColumn(name="tax_code_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $taxCode;
    
    /**
     * @var \Extend\Entity\EV_Marello_Item_Status
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
    protected $status;

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
     * @ORM\Column(name="allocation_exclusion", type="boolean", nullable=true, options={"default"=false})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=false
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var boolean
     */
    protected $allocationExclusion = false;

    /**
     * @var string
     *
     * @ORM\Column(name="item_type",type="string", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=false
     *          }
     *      }
     * )
     */
    protected $itemType = OrderItemTypeInterface::OI_TYPE_DELIVERY;

    /**
     * OrderItem constructor.
     */
    public function __construct()
    {
        $this->returnItems = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (null === $this->product) {
            return;
        }

        // prevent overriding product name if already being set
        if (is_null($this->productName)) {
            $this->setProductName((string)$this->product->getName());
        }
        $this->setProductSku($this->product->getSku());
    }

    /**
     * @return InventoryItem|null
     */
    public function getInventoryItem()
    {
        return $this->getProduct()->getInventoryItem();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     *
     * @return $this
     */
    public function setOrder($order = null)
    {
        $this->order = $order;

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
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $price
     *
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return int
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param int $tax
     *
     * @return $this
     */
    public function setTax($tax)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * @return TaxCode
     */
    public function getTaxCode()
    {
        return $this->taxCode;
    }

    /**
     * @param TaxCode $taxCode
     *
     * @return $this
     */
    public function setTaxCode(TaxCode $taxCode)
    {
        $this->taxCode = $taxCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getRowTotalInclTax()
    {
        return $this->rowTotalInclTax;
    }

    /**
     * @param int $rowTotalInclTax
     *
     * @return $this
     */
    public function setRowTotalInclTax($rowTotalInclTax)
    {
        $this->rowTotalInclTax = $rowTotalInclTax;

        return $this;
    }

    /**
     * @return int
     */
    public function getRowTotalExclTax()
    {
        return $this->rowTotalExclTax;
    }

    /**
     * @param int $rowTotalExclTax
     *
     * @return $this
     */
    public function setRowTotalExclTax($rowTotalExclTax)
    {
        $this->rowTotalExclTax = $rowTotalExclTax;

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
     * @return string
     */
    public function getProductSku()
    {
        return $this->productSku;
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * @param $productName
     * @return $this
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;
        
        return $this;
    }

    /**
     * @param $productSku
     * @return $this
     */
    public function setProductSku($productSku)
    {
        $this->productSku = $productSku;

        return $this;
    }
    
    /**
     * @return Collection|ReturnItem[]
     */
    public function getReturnItems()
    {
        return $this->returnItems;
    }

    /**
     * @param Collection|ReturnItem[] $items
     *
     * @return $this
     */
    public function setReturnItems($items)
    {
        $this->returnItems = $items;

        return $this;
    }

    /**
     * @param ReturnItem $item
     *
     * @return $this
     */
    public function addReturnItem(ReturnItem $item)
    {
        if (!$this->returnItems->contains($item)) {
            $this->returnItems->add($item->setOrderItem($this));
        }

        return $this;
    }

    /**
     * @param ReturnItem $item
     *
     * @return $this
     */
    public function removeReturnItem(ReturnItem $item)
    {
        if ($this->returnItems->contains($item)) {
            $this->returnItems->removeElement($item);
        }

        return $this;
    }

    /**
     * @return float
     */
    public function getTaxPercent()
    {
        return $this->taxPercent;
    }

    /**
     * @param float $taxPercent
     */
    public function setTaxPercent($taxPercent)
    {
        $this->taxPercent = $taxPercent;
    }

    /**
     * @return int
     */
    public function getOriginalPriceInclTax()
    {
        return $this->originalPriceInclTax;
    }

    /**
     * @param int $originalPriceInclTax
     */
    public function setOriginalPriceInclTax($originalPriceInclTax)
    {
        $this->originalPriceInclTax = $originalPriceInclTax;
    }
    /**
     * @return int
     */
    public function getOriginalPriceExclTax()
    {
        return $this->originalPriceExclTax;
    }

    /**
     * @param int $originalPriceExclTax
     */
    public function setOriginalPriceExclTax($originalPriceExclTax)
    {
        $this->originalPriceExclTax = $originalPriceExclTax;
    }

    /**
     * @return int
     */
    public function getPurchasePriceIncl()
    {
        return $this->purchasePriceIncl;
    }

    /**
     * @param int $purchasePriceIncl
     */
    public function setPurchasePriceIncl($purchasePriceIncl)
    {
        $this->purchasePriceIncl = $purchasePriceIncl;
    }

    /**
     * @return float
     */
    public function getDiscountPercent()
    {
        return $this->discountPercent;
    }

    /**
     * @param float $discountPercent
     */
    public function setDiscountPercent($discountPercent)
    {
        $this->discountPercent = $discountPercent;
    }

    /**
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * @param float $discountAmount
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;
    }

    /**
     * Get currency for orderItem from Order
     */
    public function getCurrency()
    {
        return $this->order->getCurrency();
    }

    /**
     * @return \Extend\Entity\EV_Marello_Item_Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        
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

    /**
     * @return bool
     */
    public function isAllocationExclusion(): bool
    {
        return $this->allocationExclusion;
    }

    /**
     * @param bool $allocationExclusion
     * @return $this
     */
    public function setAllocationExclusion(bool $allocationExclusion = false): self
    {
        $this->allocationExclusion = $allocationExclusion;

        return $this;
    }

    /**
     * @return string
     */
    public function getItemType(): ?string
    {
        return $this->itemType;
    }

    /**
     * @param string|null $itemType
     * @return $this
     */
    public function setItemType(string $itemType = null): self
    {
        $this->itemType = $itemType;

        return $this;
    }
}
