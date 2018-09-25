<?php

namespace Marello\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CatalogBundle\Entity\Category;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Model\InventoryItemAwareInterface;
use Marello\Bundle\PricingBundle\Entity\AssembledChannelPriceList;
use Marello\Bundle\PricingBundle\Entity\AssembledPriceList;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Model\PricingAwareInterface;
use Marello\Bundle\ProductBundle\Model\ExtendProduct;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Model\SalesChannelAwareInterface;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

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
 *      "dataaudit"={
 *            "auditable"=true
 *      },
 *      "entity"={"icon"="fa-barcode"},
 *      "ownership"={
 *              "owner_type"="ORGANIZATION",
 *              "owner_field_name"="organization",
 *              "owner_column_name"="organization_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      },
 *  }
 * )
 */
class Product extends ExtendProduct implements
    ProductInterface,
    SalesChannelAwareInterface,
    PricingAwareInterface,
    OrganizationAwareInterface,
    InventoryItemAwareInterface
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
     *          "dataaudit"={
     *              "auditable"=true
     *          },
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
     *          "dataaudit"={
     *              "auditable"=true
     *          },
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
     * @var string
     *
     * @ORM\Column(name="manufacturing_code", type="string", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $manufacturingCode;

    /**
     * @var ProductStatus
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\ProductStatus")
     * @ORM\JoinColumn(name="product_status", referencedColumnName="name")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
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
     *          "dataaudit"={
     *              "auditable"=true
     *          },
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
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $cost;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var float
     */
    protected $weight;

    /**
     * @var integer
     *
     * @ORM\Column(name="warranty", type="integer", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $warranty;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\ConfigField(
     *  defaultValues={
     *      "dataaudit"={
     *          "auditable"=true
     *      },
     *      "importexport"={
     *          "excluded"=true
     *      }
     *  }
     * )
     */
    protected $organization;

    /**
     * @var ArrayCollection|AssembledPriceList[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\PricingBundle\Entity\AssembledPriceList",
     *     mappedBy="product",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $prices;

    /**
     * @var ArrayCollection|AssembledChannelPriceList[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\PricingBundle\Entity\AssembledChannelPriceList",
     *     mappedBy="product",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $channelPrices;

    /**
     * @var ArrayCollection
     * unidirectional many-to-many
     * @ORM\ManyToMany(targetEntity="Marello\Bundle\SalesBundle\Entity\SalesChannel")
     * @ORM\JoinTable(name="marello_product_saleschannel")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $channels;

    /**
     * @var Variant
     *
     * @ORM\ManyToOne(targetEntity="Variant", inversedBy="products")
     * @ORM\JoinColumn(name="variant_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $variant;

    /**
     * @var ArrayCollection|InventoryItem[]
     *
     * @ORM\OneToMany(
     *      targetEntity="Marello\Bundle\InventoryBundle\Entity\InventoryItem",
     *      mappedBy="product",
     *      cascade={"remove", "persist"},
     *      orphanRemoval=true
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
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
     * @var ArrayCollection|ProductSupplierRelation[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation",
     *     mappedBy="product",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $suppliers;

    /**
     * @var Supplier
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\SupplierBundle\Entity\Supplier")
     * @ORM\JoinColumn(name="preferred_supplier_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $preferredSupplier;

    /**
     * @var TaxCode
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\TaxBundle\Entity\TaxCode")
     * @ORM\JoinColumn(name="tax_code_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $taxCode;

    /**
     * @var ArrayCollection|ProductChannelTaxRelation[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\ProductBundle\Entity\ProductChannelTaxRelation",
     *     mappedBy="product",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $salesChannelTaxCodes;

    /**
     * @var string
     */
    protected $replenishment;

    /**
     * @var ArrayCollection|Category[]
     *
     * @ORM\ManyToMany(targetEntity="Marello\Bundle\CatalogBundle\Entity\Category", mappedBy="products")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $categories;

    public function __construct()
    {
        $this->prices               = new ArrayCollection();
        $this->channelPrices        = new ArrayCollection();
        $this->channels             = new ArrayCollection();
        $this->inventoryItems       = new ArrayCollection();
        $this->suppliers            = new ArrayCollection();
        $this->salesChannelTaxCodes = new ArrayCollection();
        $this->categories           = new ArrayCollection();
    }
    
    public function __clone()
    {
        if ($this->id) {
            $this->id                   = null;
            $this->prices               = new ArrayCollection();
            $this->channelPrices        = new ArrayCollection();
            $this->channels             = new ArrayCollection();
            $this->inventoryItems       = new ArrayCollection();
            $this->suppliers            = new ArrayCollection();
            $this->salesChannelTaxCodes = new ArrayCollection();
        }
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
     * @return string
     */
    public function getManufacturingCode()
    {
        return $this->manufacturingCode;
    }

    /**
     * @param string $manufacturingCode
     */
    public function setManufacturingCode($manufacturingCode)
    {
        $this->manufacturingCode = $manufacturingCode;
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
     * @param string $currency
     * @return AssembledPriceList
     */
    public function getPrice($currency = null)
    {
        if ($currency) {
            /** @var  $productPrice */
            $productPrice = $this->getPrices()
                ->filter(function ($productPrice) use ($currency) {
                    /** @var AssembledPriceList $productPrice */
                    return $productPrice->getCurrency() === $currency;
                })
                ->first();

            if ($productPrice) {
                return $productPrice;
            }
        }
        
        return $this->prices->first();
    }

    /**
     * @return ArrayCollection|AssembledPriceList[]
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * Add item
     *
     * @param AssembledPriceList $price
     *
     * @return Product
     */
    public function addPrice(AssembledPriceList $price)
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
     * @param AssembledPriceList $price
     *
     * @return Product
     */
    public function removePrice(AssembledPriceList $price)
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
     * @return ArrayCollection|AssembledChannelPriceList[]
     */
    public function getChannelPrices()
    {
        return $this->channelPrices;
    }

    /**
     * Add item
     *
     * @param AssembledChannelPriceList $channelPrice
     *
     * @return Product
     */
    public function addChannelPrice(AssembledChannelPriceList $channelPrice)
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
     * @param AssembledChannelPriceList $channelPrice
     *
     * @return Product
     */
    public function removeChannelPrice(AssembledChannelPriceList $channelPrice)
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
     * @return ArrayCollection|SalesChannel[]
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
     * @return OrganizationInterface
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param OrganizationInterface $organization
     * @return Product
     */
    public function setOrganization(OrganizationInterface $organization)
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
        if (!$this->inventoryItems->contains($item)) {
            $this->inventoryItems->add($item->setProduct($this));
        }

        return $this;
    }

    /**
     * @param InventoryItem $item
     *
     * @return $this
     */
    public function removeInventoryItem(InventoryItem $item)
    {
        if ($this->inventoryItems->contains($item)) {
            $this->inventoryItems->removeElement($item);
        }
        
        return $this;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     *
     * @return Product
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return integer
     */
    public function getWarranty()
    {
        return $this->warranty;
    }

    /**
     * @param integer $warranty
     *
     * @return Product
     */
    public function setWarranty($warranty)
    {
        $this->warranty = $warranty;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSuppliers()
    {
        return $this->suppliers;
    }

    /**
     * Add item
     *
     * @param ProductSupplierRelation $supplier
     *
     * @return Product
     */
    public function addSupplier(ProductSupplierRelation $supplier)
    {
        if (!$this->suppliers->contains($supplier)) {
            $this->suppliers->add($supplier);
            $supplier->setProduct($this);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSuppliers()
    {
        return count($this->suppliers) > 0;
    }

    /**
     * Remove item
     *
     * @param ProductSupplierRelation $supplier
     *
     * @return Product
     */
    public function removeSupplier(ProductSupplierRelation $supplier)
    {
        if ($this->suppliers->contains($supplier)) {
            $this->suppliers->removeElement($supplier);
        }

        return $this;
    }
    
    /**
     * @return Supplier
     */
    public function getPreferredSupplier()
    {
        return $this->preferredSupplier;
    }

    /**
     * @param Supplier $preferredSupplier
     *
     * @return Product
     */
    public function setPreferredSupplier(Supplier $preferredSupplier)
    {
        $this->preferredSupplier = $preferredSupplier;

        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdateTimestamp()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersistTimestamp()
    {
        $this->createdAt = $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Set taxCode
     *
     * @param TaxCode $taxCode
     *
     * @return Product
     */
    public function setTaxCode(TaxCode $taxCode = null)
    {
        $this->taxCode = $taxCode;

        return $this;
    }

    /**
     * Get taxCode
     *
     * @return TaxCode
     */
    public function getTaxCode()
    {
        return $this->taxCode;
    }

    /**
     * Add salesChannelTaxCode
     *
     * @param ProductChannelTaxRelation $salesChannelTaxCode
     *
     * @return Product
     */
    public function addSalesChannelTaxCode(ProductChannelTaxRelation $salesChannelTaxCode)
    {
        if (!$this->salesChannelTaxCodes->contains($salesChannelTaxCode)) {
            $this->salesChannelTaxCodes->add($salesChannelTaxCode);
            $salesChannelTaxCode->setProduct($this);
        }

        return $this;
    }

    /**
     * Remove salesChannelTaxCode
     *
     * @param ProductChannelTaxRelation $salesChannelTaxCode
     *
     * @return Product
     */
    public function removeSalesChannelTaxCode(ProductChannelTaxRelation $salesChannelTaxCode)
    {
        if ($this->salesChannelTaxCodes->contains($salesChannelTaxCode)) {
            $this->salesChannelTaxCodes->removeElement($salesChannelTaxCode);
        }

        return $this;
    }

    /**
     * Get salesChannelTaxCodes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSalesChannelTaxCodes()
    {
        return $this->salesChannelTaxCodes;
    }

    /**
     * Get salesChannelTaxCode
     *
     * @param SalesChannel $salesChannel
     * @return TaxCode|null
     */
    public function getSalesChannelTaxCode(SalesChannel $salesChannel)
    {
        /** @var ProductChannelTaxRelation $productChannelTaxRelation */
        $productChannelTaxRelation = $this->getSalesChannelTaxCodes()
            ->filter(function ($productChannelTaxRelation) use ($salesChannel) {
                /** @var ProductChannelTaxRelation $productChannelTaxRelation */
                return $productChannelTaxRelation->getSalesChannel() === $salesChannel;
            })
            ->first();

        if ($productChannelTaxRelation) {
            return $productChannelTaxRelation->getTaxCode();
        }

        return null;
    }

    /**
     * @param SalesChannel $salesChannel
     * @return ProductChannelPrice|null
     */
    public function getSalesChannelPrice(SalesChannel $salesChannel)
    {
        /** @var ProductChannelPrice $productChannelPrice */
        $productChannelPrice = $this->getChannelPrices()
            ->filter(function ($productChannelPrice) use ($salesChannel) {
                /** @var ProductChannelPrice $productChannelPrice */
                return $productChannelPrice->getChannel() === $salesChannel;
            })
            ->first();

        if ($productChannelPrice) {
            return $productChannelPrice;
        }

        return null;
    }
    
    /**
     * @return Collection|Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function addCategory(Category $category)
    {
        if (!$this->hasCategory($category)) {
            $this->categories->add($category);
            $category->addProduct($this);
        }

        return $this;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function removeCategory(Category $category)
    {
        if ($this->hasCategory($category)) {
            $this->categories->removeElement($category);
            $category->removeProduct($this);
        }

        return $this;
    }

    /**
     * @param Category $category
     * @return bool
     */
    public function hasCategory(Category $category)
    {
        return $this->categories->contains($category);
    }
}
