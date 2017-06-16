<?php

namespace Marello\Bundle\OrderBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Marello\Bundle\InventoryBundle\Entity\InventoryItemAwareInterface;
use Marello\Bundle\OrderBundle\Model\ExtendOrderItem;
use Marello\Bundle\PricingBundle\Model\CurrencyAwareInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * @ORM\Entity()
 * @Oro\Config()
 * @ORM\Table(name="marello_order_order_item")
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("ALL")
 */
class OrderItem extends ExtendOrderItem implements
    CurrencyAwareInterface,
    InventoryItemAwareInterface
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JMS\Expose
     */
    protected $id;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\Product")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @JMS\Expose
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
     *          }
     *      }
     * )
     */
    protected $order;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity",type="integer",nullable=false)
     *
     * @JMS\Expose
     */
    protected $quantity;

    /**
     * @var int
     *
     * @ORM\Column(name="price",type="money")
     *
     * @JMS\Expose
     */
    protected $price;

    /**
     * @var int
     *
     * @ORM\Column(name="original_price_incl_tax",type="money")
     *
     * @JMS\Expose
     */
    protected $originalPriceInclTax;

    /**
     * @var int
     *
     * @ORM\Column(name="original_price_excl_tax",type="money")
     *
     * @JMS\Expose
     */
    protected $originalPriceExclTax;

    /**
     * @var int
     *
     * @ORM\Column(name="purchase_price_incl",type="money")
     *
     * @JMS\Expose
     */
    protected $purchasePriceIncl;

    /**
     * @var int
     *
     * @ORM\Column(name="tax",type="money")
     *
     * @JMS\Expose
     */
    protected $tax;

    /**
     * @var float
     *
     * @ORM\Column(name="tax_percent", type="percent", nullable=true)
     * @JMS\Expose
     */
    protected $taxPercent;

    /**
     * @var float
     *
     * @ORM\Column(name="discount_percent", type="percent", nullable=true)
     * @JMS\Expose
     */
    protected $discountPercent;

    /**
     * @var double
     *
     * @ORM\Column(name="discount_amount", type="money", nullable=true)
     * @JMS\Expose
     */
    protected $discountAmount;

    /**
     * @var int
     *
     * @ORM\Column(name="row_total_incl_tax",type="money", nullable=false)
     *
     * @JMS\Expose
     */
    protected $rowTotalInclTax;

    /**
     * @var int
     *
     * @ORM\Column(name="row_total_excl_tax",type="money", nullable=false)
     *
     * @JMS\Expose
     */
    protected $rowTotalExclTax;
    
    /**
     * @var ReturnItem[]|Collection
     *
     * @ORM\OneToMany(targetEntity="Marello\Bundle\ReturnBundle\Entity\ReturnItem", mappedBy="orderItem", cascade={})
     */
    protected $returnItems;

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
        // prevent overriding product name if already being set
        if (is_null($this->productName)) {
            $this->setProductName($this->product->getName());
        }
        $this->productSku  = $this->product->getSku();
    }

    /**
     * @return ArrayCollection|\Marello\Bundle\InventoryBundle\Entity\InventoryItem[]
     */
    public function getInventoryItems()
    {
        return $this->getProduct()->getInventoryItems();
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
    public function setProduct($product)
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
     * @return Collection|ReturnItem[]
     */
    public function getReturnItems()
    {
        return $this->returnItems;
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
}
