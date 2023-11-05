<?php

namespace Marello\Bundle\PackingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Marello\Bundle\SalesBundle\Model\SalesChannelAwareInterface;
use Marello\Bundle\CoreBundle\DerivedProperty\DerivedPropertyAwareInterface;

/**
 * @ORM\Entity()
 * @Oro\Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-list-alt"
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
 * @ORM\Table(name="marello_packing_packing_slip")
 * @ORM\HasLifecycleCallbacks()
 */
class PackingSlip implements
    DerivedPropertyAwareInterface,
    OrganizationAwareInterface,
    SalesChannelAwareInterface,
    ExtendEntityInterface
{
    use EntityCreatedUpdatedAtTrait;
    use AuditableOrganizationAwareTrait;
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
     * @var Collection|PackingSlipItem[]
     *
     * @ORM\OneToMany(targetEntity="PackingSlipItem", mappedBy="packingSlip", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $items;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\OrderBundle\Entity\Order", cascade={"persist"})
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false)
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
     * @var AbstractAddress
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\AddressBundle\Entity\MarelloAddress", cascade={"persist"})
     * @ORM\JoinColumn(name="billing_address_id", referencedColumnName="id")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $billingAddress;

    /**
     * @var AbstractAddress
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\AddressBundle\Entity\MarelloAddress", cascade={"persist"})
     * @ORM\JoinColumn(name="shipping_address_id", referencedColumnName="id")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $shippingAddress;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\CustomerBundle\Entity\Customer", cascade={"persist"})
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
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
     * @ORM\Column(name="saleschannel_name", type="string", nullable=true)
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
     * @var Warehouse
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\Warehouse")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $warehouse;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
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
    protected $comment;

    /**
     * @ORM\Column(name="packing_slip_number", type="string", nullable=true)
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
    protected $packingSlipNumber;

    /**
     * @var Allocation
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\Allocation")
     * @ORM\JoinColumn(name="source_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=false
     *          }
     *      }
     * )
     */
    protected $sourceEntity;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Collection|PackingSlipItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param PackingSlipItem $item
     *
     * @return $this
     */
    public function addItem(PackingSlipItem $item)
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setPackingSlip($this);
        }

        return $this;
    }

    /**
     * @param PackingSlipItem $item
     *
     * @return $this
     */
    public function removeItem(PackingSlipItem $item)
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
        }

        return $this;
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
    public function setOrder(Order $order)
    {
        $this->order = $order;

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
    public function setShippingAddress(MarelloAddress $shippingAddress)
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
    public function setBillingAddress(MarelloAddress $billingAddress)
    {
        $this->billingAddress = $billingAddress;

        return $this;
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
        if ($this->salesChannel) {
            $this->salesChannelName = $this->salesChannel->getName();
        }

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
    public function setWarehouse($warehouse)
    {
        $this->warehouse = $warehouse;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPackingSlipNumber()
    {
        return $this->packingSlipNumber;
    }

    /**
     * @param $packingSlipNumber
     * @return $this
     */
    public function setPackingSlipNumber($packingSlipNumber)
    {
        $this->packingSlipNumber = $packingSlipNumber;

        return $this;
    }

    /**
     * @return Allocation|null
     */
    public function getSourceEntity(): ?Allocation
    {
        return $this->sourceEntity;
    }

    /**
     * @param Allocation|null $sourceEntity
     */
    public function setSourceEntity(Allocation $sourceEntity = null): self
    {
        $this->sourceEntity = $sourceEntity;

        return $this;
    }

    /**
     * @param $id
     */
    public function setDerivedProperty($id)
    {
        if (!$this->packingSlipNumber) {
            $this->setPackingSlipNumber(sprintf('%09d', $id));
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('#%s', $this->packingSlipNumber);
    }
}
