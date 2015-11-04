<?php

namespace Marello\Bundle\PricingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
/**
 * Represents a Marello ProductPrice
 *
 * @ORM\Entity
 * @ORM\Table(
 *      name="marello_product_price",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="marello_product_price_uidx",
 *              columns={"product_id", "channel_id", "currency"}
 *          )
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *  defaultValues={
 *      "entity"={"icon"="icon-usd"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class ProductPrice
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    protected $id;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     **/
    protected $product;


    /**
     * @var SalesChannel
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\SalesBundle\Entity\SalesChannel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $channel;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="money")
     */
    protected $value;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=3)
     */
    protected $currency;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return ProductPrice
     */
    public function setProduct(Product $product)
    {
        $this->product    = $product;

        return $this;
    }

    /**
     * @return SalesChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param SalesChannel $channel
     *
     * @return $this
     */
    public function setChannel(SalesChannel $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

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
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}
