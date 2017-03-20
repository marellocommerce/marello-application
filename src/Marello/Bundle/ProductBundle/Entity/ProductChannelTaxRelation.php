<?php

namespace Marello\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * TaxCode
 *
 * @ORM\Entity(repositoryClass="Marello\Bundle\ProductBundle\Entity\Repository\ProductChannelTaxRelationRepository")
 * @ORM\Table(
 *     name="marello_prod_prod_chan_tax_rel",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="marello_prod_prod_chan_tax_rel_uidx",
 *              columns={"product_id", "sales_channel_id", "tax_code_id"}
 *          )
 *      }
 * )
 * @Oro\Config()
 */
class ProductChannelTaxRelation
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
     *
     */
    protected $product;

    /**
     * @var SalesChannel
     *
     * @ORM\OneToOne(targetEntity="Marello\Bundle\SalesBundle\Entity\SalesChannel", cascade={"persist"})
     * @ORM\JoinColumn(name="sales_channel_id", nullable=false)
     *
     */
    protected $salesChannel;

    /**
     * @var TaxCode
     *
     * @ORM\OneToOne(targetEntity="Marello\Bundle\TaxBundle\Entity\TaxCode", cascade={"persist"})
     * @ORM\JoinColumn(name="tax_code_id", nullable=false)
     *
     */
    protected $taxCode;

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
     * @param \Marello\Bundle\ProductBundle\Entity\Product $product
     *
     * @return ProductChannelTaxRelation
     */
    public function setProduct(\Marello\Bundle\ProductBundle\Entity\Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \Marello\Bundle\ProductBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set salesChannel
     *
     * @param \Marello\Bundle\SalesBundle\Entity\SalesChannel $salesChannel
     *
     * @return ProductChannelTaxRelation
     */
    public function setSalesChannel(\Marello\Bundle\SalesBundle\Entity\SalesChannel $salesChannel)
    {
        $this->salesChannel = $salesChannel;

        return $this;
    }

    /**
     * Get salesChannel
     *
     * @return \Marello\Bundle\SalesBundle\Entity\SalesChannel
     */
    public function getSalesChannel()
    {
        return $this->salesChannel;
    }

    /**
     * Set taxCode
     *
     * @param \Marello\Bundle\TaxBundle\Entity\TaxCode $taxCode
     *
     * @return ProductChannelTaxRelation
     */
    public function setTaxCode(\Marello\Bundle\TaxBundle\Entity\TaxCode $taxCode)
    {
        $this->taxCode = $taxCode;

        return $this;
    }

    /**
     * Get taxCode
     *
     * @return \Marello\Bundle\TaxBundle\Entity\TaxCode
     */
    public function getTaxCode()
    {
        return $this->taxCode;
    }
}
