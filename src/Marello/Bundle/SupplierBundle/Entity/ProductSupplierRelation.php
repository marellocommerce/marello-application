<?php

namespace Marello\Bundle\SupplierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SupplierBundle\Entity\Supplier;

/**
 * ProductSupplierRelation
 *
 * @ORM\Table(
 *     name="marello_supplier_prod_supp_rel",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="marello_supplier_prod_supp_rel_uidx",
 *              columns={"product_id", "supplier_id", "quantity_of_unit"}
 *          )
 *      }
 * )
 * @ORM\Entity
 */
class ProductSupplierRelation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Product
     *
     * @ORM\OneToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\Product", cascade={"persist"})
     * @ORM\JoinColumn(name="product_id", nullable=false)
     */
    protected $product;

    /**
     * @var Supplier
     *
     * @ORM\OneToOne(targetEntity="Marello\Bundle\SupplierBundle\Entity\Supplier", cascade={"persist"})
     * @ORM\JoinColumn(name="supplier_id", nullable=false)
     */
    protected $supplier;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity_of_unit", type="integer", nullable=false)
     */
    protected $quantityOfUnit;

    /**
     * @var integer
     *
     * @ORM\Column(name="priority", type="integer")
     */
    protected $priority;

    /**
     * @var double
     *
     * @ORM\Column(name="cost", type="money", nullable=true)
     */
    protected $cost;

    /**
     * @var boolean
     *
     * @ORM\Column(name="can_dropship", type="boolean", nullable=false)
     */
    protected $canDropship;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set product
     *
     * @param Product $product
     *
     * @return ProductSupplierRelation
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set supplier
     *
     * @param Supplier $supplier
     *
     * @return ProductSupplierRelation
     */
    public function setSupplier(Supplier $supplier)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * Get supplier
     *
     * @return Supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * Set quantityOfUnit
     *
     * @param integer $quantityOfUnit
     *
     * @return ProductSupplierRelation
     */
    public function setQuantityOfUnit($quantityOfUnit)
    {
        $this->quantityOfUnit = $quantityOfUnit;

        return $this;
    }

    /**
     * Get quantityOfUnit
     *
     * @return integer
     */
    public function getQuantityOfUnit()
    {
        return $this->quantityOfUnit;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     *
     * @return ProductSupplierRelation
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set cost
     *
     * @param double $cost
     *
     * @return ProductSupplierRelation
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return double
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set canDropship
     *
     * @param boolean $canDropship
     *
     * @return ProductSupplierRelation
     */
    public function setCanDropship($canDropship)
    {
        $this->canDropship = $canDropship;

        return $this;
    }

    /**
     * Get canDropship
     *
     * @return boolean
     */
    public function getCanDropship()
    {
        return $this->canDropship;
    }
}
