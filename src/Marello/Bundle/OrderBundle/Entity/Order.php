<?php

namespace Marello\Bundle\OrderBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;

/**
 * @ORM\Entity
 * @ORM\Table(name="marello_order_order")
 */
class Order
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
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
    protected $subtotal;

    /**
     * @var int
     *
     * @ORM\Column(type="money")
     */
    protected $totalTax;

    /**
     * @var int
     *
     * @ORM\Column(type="money")
     */
    protected $grandtotal;

    /**
     * @var Collection|OrderItem[]
     *
     * @ORM\OneToMany(targetEntity="OrderItem", mappedBy="order")
     */
    protected $items;

    /**
     * @var AbstractTypedAddress
     */
    protected $billingAddress;

    /**
     * @var AbstractTypedAddress
     */
    protected $shippingAddress;

    /*
     * TODO: Relation with sales channel.
     */

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
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
    public function getGrandtotal()
    {
        return $this->grandtotal;
    }

    /**
     * @param int $grandtotal
     *
     * @return $this
     */
    public function setGrandtotal($grandtotal)
    {
        $this->grandtotal = $grandtotal;

        return $this;
    }

    /**
     * @return AbstractTypedAddress
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @param AbstractTypedAddress $shippingAddress
     *
     * @return $this
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * @return AbstractTypedAddress
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param AbstractTypedAddress $billingAddress
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
    public function addItem(OrderItem $item) {
        $this->items->add($item);
        $item->setOrder($this);

        return $this;
    }
}
