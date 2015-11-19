<?php

namespace Marello\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\ProductBundle\Model\ExtendProduct;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Model\SalesChannelAwareInterface;

/**
 * Represents a Marello Product
 *
 * @ORM\Entity
 * @ORM\Table(
 *      name="marello_product_product",
 *      indexes={
 *          @ORM\Index(name="idx_marello_product_created_at", columns={"created_at"}),
 *          @ORM\Index(name="idx_marello_product_updated_at", columns={"updated_at"})
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="marello_product_product_skuidx",
 *              columns={"sku"}
 *          )
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *  routeName="marello_product_index",
 *  routeView="marello_product_view",
 *  defaultValues={
 *      "entity"={"icon"="icon-barcode"},
 *      "ownership"={
 *          "organization_field_name"="owner",
 *          "organization_column_name"="owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      },
 *  }
 * )
 */
class Product extends ExtendProduct implements SalesChannelAwareInterface
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="sku", type="string", nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $sku;

    /**
     * @var double
     *
     * @ORM\Column(name="price", type="money", nullable=false)
     */
    protected $price;

    /**
     * @var ProductStatus
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\ProductStatus")
     * @ORM\JoinColumn(name="marello_product_status_name", referencedColumnName="name")
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=80,
     *          "short"=true
     *      }
     *  }
     * )
     **/
    protected $status;


    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var Collection|ProductPrice[]
     *
     * @ORM\OneToMany(targetEntity="Marello\Bundle\PricingBundle\Entity\ProductPrice", mappedBy="product",
     *                                                                                 cascade={"persist"},
     *                                                                                 orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $prices;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Marello\Bundle\SalesBundle\Entity\SalesChannel")
     */
    protected $channels;

    /**
     * @var Variant
     *
     * @ORM\ManyToOne(targetEntity="Variant")
     * @ORM\JoinColumn(name="variant_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $variant;

    /**
     * @var Collection|InventoryItem[]
     *
     * @ORM\OneToMany(
     *      targetEntity="Marello\Bundle\InventoryBundle\Entity\InventoryItem",
     *      mappedBy="product",
     *      cascade={"remove", "persist"},
     *      orphanRemoval=true,
     *      fetch="EXTRA_LAZY"
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $inventoryItems;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->prices         = new ArrayCollection();
        $this->channels       = new ArrayCollection();
        $this->inventoryItems = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     *
     * @return Product
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

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
     * @param float $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return ProductStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param ProductStatus $status
     *
     * @return Product
     */
    public function setStatus(ProductStatus $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * Add item
     *
     * @param ProductPrice $price
     *
     * @return Product
     */
    public function addPrice(ProductPrice $price)
    {
        if (!$this->prices->contains($price)) {
            $this->prices->add($price);
            $price->setProduct($this);
        }

        return $this;
    }

    /**
     * Remove item
     *
     * @param ProductPrice $price
     *
     * @return Product
     */
    public function removePrice(ProductPrice $price)
    {
        if ($this->prices->contains($price)) {
            $this->prices->removeElement($price);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @return Variant
     */
    public function getVariant()
    {
        return $this->variant;
    }

    /**
     * @param Variant $variant
     *
     * @return Product
     */
    public function setVariant(Variant $variant)
    {
        $this->variant = $variant;

        return $this;
    }

    /**
     * Add item
     *
     * @param SalesChannel $channel
     *
     * @return Product
     */
    public function addChannel(SalesChannel $channel)
    {
        if (!$this->channels->contains($channel)) {
            $this->channels->add($channel);
        }

        return $this;
    }

    /**
     * Remove item
     *
     * @param SalesChannel $channel
     *
     * @return Product
     */
    public function removeChannel(SalesChannel $channel)
    {
        if ($this->channels->contains($channel)) {
            $this->channels->removeElement($channel);
        }

        return $this;
    }

    /**
     * @return Organization
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Organization $owner
     *
     * @return Product
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

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
     *
     * @return Product
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
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
     *
     * @return Product
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
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

    /**
     * @return string
     */
    function __toString()
    {
        return $this->name;
    }

    /**
     * @return Collection|InventoryItem[]
     */
    public function getInventoryItems()
    {
        return $this->inventoryItems;
    }

    /**
     * @param Collection|InventoryItem[] $inventoryItems
     *
     * @return $this
     */
    public function setInventoryItems($inventoryItems)
    {
        $this->inventoryItems = $inventoryItems;

        return $this;
    }

}
