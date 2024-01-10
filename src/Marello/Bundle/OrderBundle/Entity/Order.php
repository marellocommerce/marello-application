<?php

namespace Marello\Bundle\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\TaxBundle\Model\TaxAwareInterface;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\LocaleBundle\Model\LocalizationTrait;
use Marello\Bundle\ShippingBundle\Entity\HasShipmentTrait;
use Marello\Bundle\OrderBundle\Model\DiscountAwareInterface;
use Marello\Bundle\PricingBundle\Model\CurrencyAwareInterface;
use Marello\Bundle\SalesBundle\Model\SalesChannelAwareInterface;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Marello\Bundle\LocaleBundle\Model\LocalizationAwareInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingAwareInterface;
use Marello\Bundle\PricingBundle\Subtotal\Model\SubtotalAwareInterface;
use Marello\Bundle\PricingBundle\Subtotal\Model\LineItemsAwareInterface;
use Marello\Bundle\CoreBundle\DerivedProperty\DerivedPropertyAwareInterface;
use Oro\Bundle\UserBundle\Entity\Ownership\AuditableUserAwareTrait;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository")
 * @Oro\Config(
 *      routeView="marello_order_order_view",
 *      routeName="marello_order_order_index",
 *      routeCreate="marello_order_order_create",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-shopping-cart"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="user_owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "grid"={
 *              "default"="marello-order-select-grid"
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
 * @ORM\Table(
 *      name="marello_order_order",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"order_reference", "saleschannel_id"})
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class Order implements
    DerivedPropertyAwareInterface,
    CurrencyAwareInterface,
    DiscountAwareInterface,
    SubtotalAwareInterface,
    TaxAwareInterface,
    LineItemsAwareInterface,
    LocalizationAwareInterface,
    SalesChannelAwareInterface,
    ExtendEntityInterface
{
    use LocalizationTrait;
    use EntityCreatedUpdatedAtTrait;
    use AuditableUserAwareTrait;
    use ExtendEntityTrait;
    
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="order_number", type="string", unique=true, nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $orderNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="order_reference", type="string", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $orderReference;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_reference", type="string", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $invoiceReference;

    /**
     * @var int
     *
     * @ORM\Column(name="subtotal", type="money")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $subtotal = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="total_tax", type="money")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $totalTax = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="grand_total", type="money")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $grandTotal = 0;

    /**
     * @var string
     * @ORM\Column(name="currency", type="string", length=10, nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $currency;

    /**
     * Represent locale in ICU format
     *
     * @var string
     *
     * @ORM\Column(name="locale_id", type="string", length=255, nullable=true)
     */
    protected $localeId;

    /**
     * @var string
     * @ORM\Column(name="payment_method", type="string", length=255, nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $paymentMethod;

    /**
     * @var array $paymentMethodOptions
     *
     * @ORM\Column(name="payment_method_options", type="json_array", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $paymentMethodOptions = [];

    /**
     * @var double
     *
     * @ORM\Column(name="shipping_amount_incl_tax", type="money", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $shippingAmountInclTax;

    /**
     * @var double
     *
     * @ORM\Column(name="shipping_amount_excl_tax", type="money", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $shippingAmountExclTax;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_method", type="string", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $shippingMethod;
    
    /**
     * @var string
     *
     * @ORM\Column(name="shipping_method_type", type="string", length=255, nullable=true)
     */
    protected $shippingMethodType;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_method_reference", type="string", length=255, nullable=true)
     */
    protected $shippingMethodReference;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_method_details", type="text", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=false
     *          }
     *      }
     * )
     */
    protected $shippingMethodDetails;

    /**
     * @var float
     *
     * @ORM\Column(name="estimated_shipping_cost_amount", type="money", nullable=true)
     */
    protected $estimatedShippingCostAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="override_shipping_cost_amount", type="money", nullable=true)
     */
    protected $overriddenShippingCostAmount;
    
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
     * @var string
     * @ORM\Column(name="coupon_code", type="string", length=255, nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $couponCode;

    /**
     * @var Collection|OrderItem[]
     *
     * @ORM\OneToMany(targetEntity="OrderItem", mappedBy="order", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "full"=true
     *          },
     *          "email"={
     *              "available_in_template"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $items;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\CustomerBundle\Entity\Customer", cascade={"persist"})
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var Customer
     */
    protected $customer;

    /**
     * @var MarelloAddress
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\AddressBundle\Entity\MarelloAddress", cascade={"persist"})
     * @ORM\JoinColumn(name="billing_address_id", referencedColumnName="id")
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
    protected $billingAddress;

    /**
     * @var MarelloAddress
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\AddressBundle\Entity\MarelloAddress", cascade={"persist"})
     * @ORM\JoinColumn(name="shipping_address_id", referencedColumnName="id")
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
    protected $shippingAddress;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="invoiced_at", type="datetime", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $invoicedAt;

    /**
     * @var SalesChannel
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\SalesBundle\Entity\SalesChannel")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $salesChannel;

    /**
     * @var string
     *
     * @ORM\Column(name="saleschannel_name",type="string", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $salesChannelName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="purchase_date", type="datetime", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $purchaseDate;

    /**
     * @var array $data
     *
     * @ORM\Column(name="data", type="json_array", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $data = [];

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="delivery_date", type="datetime", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $deliveryDate;

    /**
     * @var string
     *
     * @ORM\Column(name="order_note",type="text", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $orderNote;

    /**
     * @var string
     *
     * @ORM\Column(name="po_number",type="string", nullable=true, length=255)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $poNumber;

    /**
     * @var \Extend\Entity\EV_Marello_Order_Status
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $orderStatus;

    /**
     * @param AbstractAddress|null $billingAddress
     * @param AbstractAddress|null $shippingAddress
     */
    public function __construct(
        AbstractAddress $billingAddress = null,
        AbstractAddress $shippingAddress = null
    ) {
        $this->items           = new ArrayCollection();
        $this->billingAddress  = $billingAddress;
        $this->shippingAddress = $shippingAddress;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (is_null($this->purchaseDate)) {
            $this->purchaseDate = new \DateTime('now', new \DateTimeZone('UTC'));
        }
        $this->salesChannelName = $this->salesChannel->getName();
    }

    /**
     * @return int
     */
    public function getOrderReference()
    {
        return $this->orderReference;
    }

    /**
     * @param int $orderReference
     *
     * @return $this
     */
    public function setOrderReference($orderReference)
    {
        $this->orderReference = $orderReference;

        return $this;
    }

    /**
     * @return string
     */
    public function getInvoiceReference()
    {
        return $this->invoiceReference;
    }

    /**
     * @param string $invoiceReference
     *
     * @return $this
     */
    public function setInvoiceReference($invoiceReference)
    {
        $this->invoiceReference = $invoiceReference;

        return $this;
    }

    /**
     * @return int
     */
    public function getSubtotal()
    {
        return $this->subtotal;
    }

    /**
     * @param int $subtotal
     *
     * @return $this
     */
    public function setSubtotal($subtotal)
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalTax()
    {
        return $this->totalTax;
    }

    /**
     * @return int
     */
    public function getTax()
    {
        return $this->getTotalTax();
    }

    /**
     * @param int $totalTax
     *
     * @return $this
     */
    public function setTotalTax($totalTax)
    {
        $this->totalTax = $totalTax;

        return $this;
    }

    /**
     * @return int
     */
    public function getGrandTotal()
    {
        return $this->grandTotal;
    }

    /**
     * @param int $grandTotal
     *
     * @return $this
     */
    public function setGrandTotal($grandTotal)
    {
        $this->grandTotal = $grandTotal;

        return $this;
    }

    /**
     * @return MarelloAddress
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @param MarelloAddress $shippingAddress
     *
     * @return $this
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * @return MarelloAddress
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param MarelloAddress $billingAddress
     *
     * @return $this
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * @return Collection|OrderItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param OrderItem $item
     *
     * @return $this
     */
    public function addItem(OrderItem $item)
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }

        return $this;
    }

    /**
     * @param OrderItem $item
     *
     * @return $this
     */
    public function removeItem(OrderItem $item)
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return SalesChannel
     */
    public function getSalesChannel()
    {
        return $this->salesChannel;
    }

    /**
     * @param SalesChannel $salesChannel
     *
     * @return $this
     */
    public function setSalesChannel($salesChannel)
    {
        $this->salesChannel = $salesChannel;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     *
     * @return $this
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getSalesChannelName()
    {
        return $this->salesChannelName;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @param string $localeId
     *
     * @return $this
     */
    public function setLocaleId($localeId)
    {
        $this->localeId = $localeId;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocaleId()
    {
        return $this->localeId;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     *
     * @return $this
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * @return array
     */
    public function getPaymentMethodOptions()
    {
        return $this->paymentMethodOptions;
    }

    /**
     * @param array $paymentMethodOptions
     * @return Order
     */
    public function setPaymentMethodOptions(array $paymentMethodOptions = [])
    {
        $this->paymentMethodOptions = $paymentMethodOptions;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->shippingMethod;
    }

    /**
     * @param string $shippingMethod
     *
     * @return $this
     */
    public function setShippingMethod($shippingMethod)
    {
        $this->shippingMethod = $shippingMethod;

        return $this;
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
     *
     * @return $this
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;

        return $this;
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
     *
     * @return $this
     */
    public function setDiscountPercent($discountPercent)
    {
        $this->discountPercent = $discountPercent;

        return $this;
    }

    /**
     * @return string
     */
    public function getCouponCode()
    {
        return $this->couponCode;
    }

    /**
     * @param string $couponCode
     */
    public function setCouponCode($couponCode)
    {
        $this->couponCode = $couponCode;
    }

    /**
     * @return \DateTime
     */
    public function getInvoicedAt()
    {
        return $this->invoicedAt;
    }

    /**
     * @param \DateTime $invoicedAt
     *
     * @return $this
     */
    public function setInvoicedAt($invoicedAt)
    {
        $this->invoicedAt = $invoicedAt;

        return $this;
    }

    /**
     * @param int $id
     */
    public function setDerivedProperty($id)
    {
        if (!$this->orderNumber) {
            $this->setOrderNumber(sprintf('%09d', $id));
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('#%s', $this->orderNumber);
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     *
     * @return $this
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Set shippingAmountInclTax
     *
     * @param float $shippingAmountInclTax
     *
     * @return Order
     */
    public function setShippingAmountInclTax($shippingAmountInclTax)
    {
        $this->shippingAmountInclTax = $shippingAmountInclTax;

        return $this;
    }

    /**
     * Get shippingAmountInclTax
     *
     * @return float
     */
    public function getShippingAmountInclTax()
    {
        return $this->shippingAmountInclTax ? : $this->getShippingCostAmount();
    }

    /**
     * Set shippingAmountExclTax
     *
     * @param float $shippingAmountExclTax
     *
     * @return Order
     */
    public function setShippingAmountExclTax($shippingAmountExclTax)
    {
        $this->shippingAmountExclTax = $shippingAmountExclTax;

        return $this;
    }

    /**
     * Get shippingAmountExclTax
     *
     * @return float
     */
    public function getShippingAmountExclTax()
    {
        return $this->shippingAmountExclTax ? : $this->getShippingCostAmount();
    }

    /**
     * Set salesChannelName
     *
     * @param string $salesChannelName
     *
     * @return Order
     */
    public function setSalesChannelName($salesChannelName)
    {
        $this->salesChannelName = $salesChannelName;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingMethodType()
    {
        return $this->shippingMethodType;
    }

    /**
     * @param string $shippingMethodType
     * @return $this
     */
    public function setShippingMethodType($shippingMethodType)
    {
        $this->shippingMethodType = (string) $shippingMethodType;

        return $this;
    }

    /**
     * @return string
     */
    public function getShippingMethodReference(): ?string
    {
        return $this->shippingMethodReference;
    }

    /**
     * @param string $shippingMethodReference
     * @return $this
     */
    public function setShippingMethodReference(string $shippingMethodReference = null)
    {
        $this->shippingMethodReference = $shippingMethodReference;

        return $this;
    }

    /**
     * @param string $shippingMethodDetails
     * @return $this
     */
    public function setShippingMethodDetails($shippingMethodDetails = null)
    {
        $this->shippingMethodDetails = $shippingMethodDetails;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getShippingMethodDetails(): ?string
    {
        return $this->shippingMethodDetails;
    }

    /**
     * @return Price|null
     */
    public function getShippingCost()
    {
        $amount = $this->estimatedShippingCostAmount;
        if ($this->overriddenShippingCostAmount) {
            $amount = $this->overriddenShippingCostAmount;
        }
        if ($amount && $this->currency) {
            return Price::create($amount, $this->currency);
        }
        return null;
    }

    /**
     * @return float|null
     */
    public function getShippingCostAmount()
    {
        $amount = $this->estimatedShippingCostAmount;
        if ($this->overriddenShippingCostAmount) {
            $amount = $this->overriddenShippingCostAmount;
        }
        if ($amount) {
            return $amount;
        }
        return null;
    }

    /**
     * @return Price|null
     */
    public function getEstimatedShippingCost()
    {
        if ($this->estimatedShippingCostAmount && $this->currency) {
            return Price::create($this->estimatedShippingCostAmount, $this->currency);
        }
        return null;
    }

    /**
     * @return float|null
     */
    public function getEstimatedShippingCostAmount()
    {
        return $this->estimatedShippingCostAmount;
    }

    /**
     * @param float $amount
     * @return Order
     */
    public function setEstimatedShippingCostAmount($amount)
    {
        $this->estimatedShippingCostAmount = $amount;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getOverriddenShippingCostAmount()
    {
        return $this->overriddenShippingCostAmount;
    }

    /**
     * @param float $amount
     * @return Order
     */
    public function setOverriddenShippingCostAmount($amount)
    {
        $this->overriddenShippingCostAmount = $amount;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPurchaseDate()
    {
        return $this->purchaseDate ? : $this->createdAt;
    }

    /**
     * @param \DateTime|null $purchaseDate
     * @return $this
     */
    public function setPurchaseDate(\DateTime $purchaseDate = null)
    {
        $this->purchaseDate = $purchaseDate;
        
        return $this;
    }
    
    /**
     * @param array $data
     *
     * @return Order
     */
    public function setData(array $data)
    {
        $this->data = $data;

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
     * @return \DateTime
     */
    public function getDeliveryDate()
    {
        return $this->deliveryDate;
    }

    /**
     * @param \DateTime $deliveryDate
     * @return $this
     */
    public function setDeliveryDate(\DateTime $deliveryDate = null)
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderNote()
    {
        return $this->orderNote;
    }

    /**
     * @param string $orderNote
     * @return $this
     */
    public function setOrderNote(string $orderNote)
    {
        $this->orderNote = $orderNote;

        return $this;
    }

    /**
     * @return string
     */
    public function getPoNumber()
    {
        return $this->poNumber;
    }

    /**
     * @param string $poNumber
     * @return $this
     */
    public function setPoNumber(string $poNumber)
    {
        $this->poNumber = $poNumber;

        return $this;
    }

    /**
     * @return \Extend\Entity\EV_Marello_Order_Status
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * @param string $orderStatus
     * @return $this
     */
    public function setOrderStatus($orderStatus)
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }
}
