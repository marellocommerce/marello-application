<?php

namespace Marello\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\ProductBundle\Model\ExtendProduct;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Model\SalesChannelAwareInterface;
use Marello\Bundle\PricingBundle\Model\PricingAwareInterface;

/**
 * Represents a Marello Product
 *
 * @ORM\Entity(repositoryClass="Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository")
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
 * @Oro\Config(
 *  routeName="marello_product_index",
 *  routeView="marello_product_view",
 *  defaultValues={
 *      "entity"={"icon"="icon-barcode"},
 *      "ownership"={
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      },
 *  }
 * )
 */
class Product extends ExtendProduct implements
    SalesChannelAwareInterface,
    PricingAwareInterface
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Oro\ConfigField(
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
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=10,
     *              "header"="SKU",
     *              "identity"=true,
     *          }
     *      }
     * )
     */
    protected $sku;

    /**
     * @var ProductStatus
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\ProductStatus")
     * @ORM\JoinColumn(name="product_status", referencedColumnName="name")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     **/
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $type;

    /**
     * @var double
     *
     * @ORM\Column(name="cost", type="money", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $cost;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "excluded"=true
     *      }
     *  }
     * )
     */
    protected $organization;

    /**
     * @var ArrayCollection|ProductPrice[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\PricingBundle\Entity\ProductPrice",
     *     mappedBy="product",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $prices;

    /**
     * @var ArrayCollection|ProductChannelPrice[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\PricingBundle\Entity\ProductChannelPrice",
     *     mappedBy="product",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $channelPrices;

    /**
     * @var ArrayCollection
     * unidirectional many-to-many
     * @ORM\ManyToMany(targetEntity="Marello\Bundle\SalesBundle\Entity\SalesChannel")
     * @ORM\JoinTable(name="marello_product_saleschannel")
     */
    protected $channels;

    /**
     * @var Variant
     *
     * @ORM\ManyToOne(targetEntity="Variant", inversedBy="products")
     * @ORM\JoinColumn(name="variant_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $variant;

    /**
     * @var ArrayCollection|InventoryItem[]
     *
     * @ORM\OneToMany(
     *      targetEntity="Marello\Bundle\InventoryBundle\Entity\InventoryItem",
     *      mappedBy="product",
     *      cascade={"remove", "persist"},
     *      orphanRemoval=true,
     *      fetch="LAZY"
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $inventoryItems;

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
    protected $data;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->prices         = new ArrayCollection();
        $this->channelPrices  = new ArrayCollection();
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
     * @return ArrayCollection
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
     * has prices
     * @return bool
     */
    public function hasPrices()
    {
        return count($this->prices) > 0;
    }

    /**
     * @return ArrayCollection
     */
    public function getChannelPrices()
    {
        return $this->channelPrices;
    }

    /**
     * Add item
     *
     * @param ProductChannelPrice $channelPrice
     *
     * @return Product
     */
    public function addChannelPrice(ProductChannelPrice $channelPrice)
    {
        if (!$this->channelPrices->contains($channelPrice)) {
            $this->channelPrices->add($channelPrice);
            $channelPrice->setProduct($this);
        }

        return $this;
    }

    /**
     * Remove item
     *
     * @param ProductChannelPrice $channelPrice
     *
     * @return Product
     */
    public function removeChannelPrice(ProductChannelPrice $channelPrice)
    {
        if ($this->channelPrices->contains($channelPrice)) {
            $this->channelPrices->removeElement($channelPrice);
        }

        return $this;
    }

    /**
     * has channel prices
     * @return bool
     */
    public function hasChannelPrices()
    {
        return count($this->channelPrices) > 0;
    }

    /**
     * @return ArrayCollection
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
    public function setVariant(Variant $variant = null)
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
     * @return bool
     */
    public function hasChannels()
    {
        return count($this->channels) > 0;
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
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Organization $organization
     * @return Product
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @param array $data
     * @return Product
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
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return ArrayCollection|InventoryItem[]
     */
    public function getInventoryItems()
    {
        return $this->inventoryItems;
    }

    /**
     * @param InventoryItem $item
     *
     * @return $this
     */
    public function addInventoryItem(InventoryItem $item)
    {
        $item->setProduct($this);
        $this->inventoryItems->add($item);

        return $this;
    }

    /**
     * @param InventoryItem $item
     *
     * @return $this
     */
    public function removeInventoryItem(InventoryItem $item)
    {
        $this->inventoryItems->removeElement($item);

        return $this;
    }
}
