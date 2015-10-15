<?php

namespace Marello\Bundle\OrderBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * @ORM\Entity
 * @Oro\Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-list-alt"
 *          }
 *      }
 * )
 * @ORM\Table(name="marello_order_order")
 * @ORM\HasLifecycleCallbacks
 */
class Order
{
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
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    protected $orderNumber;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $orderReference;

    /**
     * @var int
     *
     * @ORM\Column(type="money")
     */
    protected $subtotal = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="money")
     */
    protected $totalTax = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="money")
     */
    protected $grandTotal = 0;

    /**
     * @var Collection|OrderItem[]
     *
     * @ORM\OneToMany(targetEntity="OrderItem", mappedBy="order", cascade={"persist"}, orphanRemoval=true)
     */
    protected $items;

    /**
     * @var AbstractAddress
     *
     * @ORM\OneToOne(targetEntity="Marello\Bundle\AddressBundle\Entity\Address", cascade={"persist", "remove"})
     */
    protected $billingAddress;

    /**
     * @var AbstractAddress
     *
     * @ORM\OneToOne(targetEntity="Marello\Bundle\AddressBundle\Entity\Address", cascade={"persist", "remove"})
     */
    protected $shippingAddress;

    /**
     * @var \DateTime $created
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime $updated
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    /**
     * @var SalesChannel
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\SalesBundle\Entity\SalesChannel")
     */
    protected $salesChannel;

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

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->createdAt = $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
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
     * @return AbstractAddress
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @param AbstractAddress $shippingAddress
     *
     * @return $this
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * @return AbstractAddress
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param AbstractAddress $billingAddress
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
        $this->items->add($item);
        $item->setOrder($this);

        return $this;
    }

    /**
     * @param OrderItem $item
     *
     * @return $this
     */
    public function removeItem(OrderItem $item)
    {
        $this->items->removeElement($item);

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
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \Datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
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
}
