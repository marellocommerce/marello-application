<?php

namespace Marello\Bundle\TaxBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;

class Taxable
{
    /**
     * @var int
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var AbstractAddress
     */
    protected $taxationAddress;

    /**
     * @var TaxCode
     */
    protected $taxCode;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var float
     */
    protected $price;

    /**
     * @var double
     */
    protected $amount;

    /**
     * @var float
     */
    protected $shippingCost;

    /**
     * @var \SplObjectStorage|Taxable[]
     */
    protected $items;

    /**
     * @var Result
     */
    protected $result;

    /**
     * @var string
     */
    protected $currency;

    public function __construct()
    {
        $this->quantity = 1.0;
        $this->price = 0.0;
        $this->amount = 0.0;
        $this->shippingCost = 0.0;

        $this->items = new ArrayCollection();
        $this->result = new Result();
    }

    /**
     * @return int
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param int $identifier
     * @return Taxable
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param string $quantity
     * @return Taxable
     */
    public function setQuantity($quantity)
    {
        $this->quantity = (float)$quantity;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param string $price
     * @return Taxable
     */
    public function setPrice($price)
    {
        $this->price = (float)$price;

        return $this;
    }

    /**
     * @return double
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     * @return Taxable
     */
    public function setAmount($amount)
    {
        $this->amount = (double)$amount;

        return $this;
    }

    /**
     * @return float
     */
    public function getShippingCost()
    {
        return $this->shippingCost;
    }

    /**
     * @param string $shippingCost
     * @return Taxable
     */
    public function setShippingCost($shippingCost)
    {
        $this->shippingCost = (float)$shippingCost;

        return $this;
    }

    /**
     * @return Collection|Taxable[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Collection $items
     * @return Taxable
     */
    public function setItems(Collection $items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @param Taxable $item
     * @return Taxable
     */
    public function addItem(Taxable $item)
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
        }

        return $this;
    }

    /**
     * @param Taxable $item
     * @return Taxable
     */
    public function removeItem(Taxable $item)
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
        }

        return $this;
    }

    /**
     * @param string $className
     * @return Taxable
     */
    public function setClassName($className)
    {
        $this->className = (string)$className;

        return $this;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param Result $result
     * @return Taxable
     */
    public function setResult(Result $result)
    {
        if ($this->result->count() === 0) {
            $this->result = $result;
        }

        return $this;
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
     * @return Taxable
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return AbstractAddress
     */
    public function getTaxationAddress()
    {
        return $this->taxationAddress;
    }

    /**
     * @param AbstractAddress $taxationAddress
     * @return Taxable
     */
    public function setTaxationAddress(AbstractAddress $taxationAddress = null)
    {
        $this->taxationAddress = $taxationAddress;

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
     * @return Taxable
     */
    public function setTaxCode(TaxCode $taxCode = null)
    {
        $this->taxCode = $taxCode;

        return $this;
    }
}
