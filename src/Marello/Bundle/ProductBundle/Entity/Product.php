<?php

namespace Marello\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DenormalizedPropertyAwareInterface;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamilyAwareInterface;

use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\CatalogBundle\Entity\Category;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\PricingBundle\Entity\AssembledPriceList;
use Marello\Bundle\PricingBundle\Entity\PriceListInterface;
use Marello\Bundle\PricingBundle\Model\PricingAwareInterface;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Marello\Bundle\SalesBundle\Model\SalesChannelsAwareInterface;
use Marello\Bundle\PricingBundle\Entity\AssembledChannelPriceList;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository")
 * @ORM\Table(
 *      name="marello_product_product",
 *      indexes={
 *          @ORM\Index(name="idx_marello_product_created_at", columns={"created_at"}),
 *          @ORM\Index(name="idx_marello_product_updated_at", columns={"updated_at"})
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="marello_product_product_skuorgidx",
 *              columns={"sku","organization_id"}
 *          )
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
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
 *     "attribute"={
 *          "has_attributes"=true
 *     },
 *     "tag"={
 *          "enabled"=true
 *     }
 *  }
 * )
 */
class Product implements
    ProductInterface,
    SalesChannelsAwareInterface,
    PricingAwareInterface,
    OrganizationAwareInterface,
    AttributeFamilyAwareInterface,
    DenormalizedPropertyAwareInterface,
    ExtendEntityInterface
{
    use ExtendEntityTrait, EntityCreatedUpdatedAtTrait;

    const DEFAULT_PRODUCT_TYPE = 'simple';
 
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $id;
    
    /**
     * This is a mirror field for performance reasons only.
     * It mirrors getDefaultName()->getString().
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      },
     *      mode="hidden"
     * )
     */
    protected $denormalizedDefaultName;
    
    /**
     * @var Collection|LocalizedFallbackValue[]
     *
     * @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     * )
     * @ORM\JoinTable(
     *      name="marello_product_product_name",
     *      joinColumns={
     *          @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="localized_value_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     *      }
     * )
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=20,
     *              "full"=true,
     *              "fallback_field"="string"
     *          },
     *          "attribute"={
     *              "is_attribute"=true
     *          },
     *          "extend"={
     *              "owner"="System"
     *          }
     *      }
     * )
     */
    protected $names;

    /**
     * @var string
     *
     * @ORM\Column(name="sku", type="string", nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=10,
     *              "header"="SKU",
     *              "identity"=true,
     *          },
     *          "attribute"={
     *              "is_attribute"=true
     *          },
     *          "extend"={
     *              "owner"="System"
     *          }
     *      }
     * )
     */
    protected $sku;

    /**
     * @var string
     *
     * @ORM\Column(name="barcode", type="string", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "attribute"={
     *              "is_attribute"=true
     *          },
     *          "extend"={
     *              "owner"="Custom"
     *          }
     *      }
     * )
     */
    protected $barcode;

    /**
     * @var string
     *
     * @ORM\Column(name="manufacturing_code", type="string", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "attribute"={
     *              "is_attribute"=true
     *          },
     *          "extend"={
     *              "owner"="Custom"
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
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "attribute"={
     *              "is_attribute"=true
     *          },
     *          "extend"={
     *              "owner"="System"
     *          }
     *      }
     * )
     **/
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     * @ConfigField(
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
     * @ORM\Column(type="float", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "attribute"={
     *              "is_attribute"=true
     *          },
     *          "extend"={
     *              "owner"="Custom"
     *          }
     *      }
     * )
     *
     * @var float
     */
    protected $weight = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="warranty", type="integer", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "attribute"={
     *              "is_attribute"=true
     *          },
     *          "extend"={
     *              "owner"="Custom"
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
     * @ConfigField(
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
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "attribute"={
     *              "is_attribute"=true
     *          },
     *          "extend"={
     *              "owner"="System"
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
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "attribute"={
     *              "is_attribute"=true
     *          },
     *          "extend"={
     *              "owner"="System"
     *          }
     *      }
     * )
     */
    protected $channelPrices;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Marello\Bundle\SalesBundle\Entity\SalesChannel",
     *     inversedBy="products",
     *     fetch="EAGER"
     * )
     * @ORM\JoinTable(name="marello_product_saleschannel")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "attribute"={
     *              "is_attribute"=true
     *          },
     *          "extend"={
     *              "owner"="System"
     *          }
     *      }
     * )
     */
    protected $channels;

    /**
     * @var string
     *
     * @ORM\Column(name="channels_codes", type="text", nullable=true)
     * @ConfigField(
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
    protected $channelsCodes;

    /**
     * @var Variant
     *
     * @ORM\ManyToOne(targetEntity="Variant", inversedBy="products")
     * @ORM\JoinColumn(name="variant_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
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
     * @var InventoryItem
     *
     * @ORM\OneToOne(
     *      targetEntity="Marello\Bundle\InventoryBundle\Entity\InventoryItem",
     *      mappedBy="product",
     *      cascade={"remove", "persist"},
     *      orphanRemoval=true
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     * @ConfigField(
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
    protected $inventoryItem;

    /**
     * @var array $data
     *
     * @ORM\Column(name="data", type="json_array", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $data = [];

    /**
     * @var ArrayCollection|ProductSupplierRelation[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation",
     *     mappedBy="product",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "attribute"={
     *              "is_attribute"=true
     *          },
     *          "extend"={
     *              "owner"="Custom"
     *          }
     *      }
     * )
     */
    protected $suppliers;

    /**
     * @var Supplier
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\SupplierBundle\Entity\Supplier")
     * @ORM\JoinColumn(name="preferred_supplier_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * @ConfigField(
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
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "attribute"={
     *              "is_attribute"=true
     *          },
     *          "extend"={
     *              "owner"="System"
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
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "attribute"={
     *              "is_attribute"=true
     *          },
     *          "extend"={
     *              "owner"="System"
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
     * @ORM\ManyToMany(targetEntity="Marello\Bundle\CatalogBundle\Entity\Category", mappedBy="products", fetch="EAGER")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "attribute"={
     *              "is_attribute"=true
     *          },
     *          "extend"={
     *              "owner"="Custom"
     *          }
     *      }
     * )
     */
    protected $categories;

    /**
     * @var string
     *
     * @ORM\Column(name="categories_codes", type="text", nullable=true)
     * @ConfigField(
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
    protected $categoriesCodes;

    /**
     * @var AttributeFamily
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily")
     * @ORM\JoinColumn(name="attribute_family_id", referencedColumnName="id", onDelete="RESTRICT")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=false
     *          },
     *          "importexport"={
     *              "order"=10
     *          }
     *      }
     *  )
     */
    protected $attributeFamily;

    /**
     * @var \DateTime
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
     * @var \DateTime
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
        $this->names                = new ArrayCollection();
        $this->prices               = new ArrayCollection();
        $this->channelPrices        = new ArrayCollection();
        $this->channels             = new ArrayCollection();
        $this->suppliers            = new ArrayCollection();
        $this->salesChannelTaxCodes = new ArrayCollection();
        $this->categories           = new ArrayCollection();
    }
    
    public function __clone()
    {
        if ($this->id) {
            $this->id                   = null;
            $this->names                = new ArrayCollection();
            $this->prices               = new ArrayCollection();
            $this->channelPrices        = new ArrayCollection();
            $this->channels             = new ArrayCollection();
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
     * @param array|LocalizedFallbackValue[] $names
     *
     * @return $this
     */
    public function setNames(array $names = [])
    {
        $this->names->clear();

        foreach ($names as $name) {
            $this->addName($name);
        }

        return $this;
    }

    /**
     * @return Collection|LocalizedFallbackValue[]
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * @param LocalizedFallbackValue $name
     *
     * @return $this
     */
    public function addName(LocalizedFallbackValue $name)
    {
        if (!$this->names->contains($name)) {
            $this->names->add($name);
        }

        return $this;
    }

    /**
     * @param LocalizedFallbackValue $name
     *
     * @return $this
     */
    public function removeName(LocalizedFallbackValue $name)
    {
        if ($this->names->contains($name)) {
            $this->names->removeElement($name);
        }

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
     * @return string|null
     */
    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    /**
     * @param string|null $barcode
     * @return $this
     */
    public function setBarcode(string $barcode = null): self
    {
        $this->barcode = $barcode;

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
            $this->addChannelCode($channel->getCode());
        }

        return $this;
    }
    
    /**
     * @param string $code
     * @return $this
     */
    public function addChannelCode($code)
    {
        if (strpos($this->channelsCodes, '|') === false) {
            $channelsCodes = [];
        } else {
            $channelsCodes = $this->channelsCodes;
            if (substr($channelsCodes, 0, 1) === '|') {
                $channelsCodes = substr($channelsCodes, 1);
            }
            if (substr($channelsCodes, -1, 1) === '|') {
                $channelsCodes = substr($channelsCodes, 0, -1);
            }
            $channelsCodes = explode("|", $channelsCodes);
        }
        if (!in_array($code, $channelsCodes)) {
            $channelsCodes[] = $code;
            $this->channelsCodes = sprintf('|%s|', implode('|', $channelsCodes));
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
     * @param SalesChannel $channel
     * @return bool
     */
    public function hasChannel(SalesChannel $channel)
    {
        return $this->channels->contains($channel);
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
            $channelsCodes = $this->channelsCodes;
            if (substr($channelsCodes, 0, 1) === '|') {
                $channelsCodes = substr($channelsCodes, 1);
            }
            if (substr($channelsCodes, -1, 1) === '|') {
                $channelsCodes = substr($channelsCodes, 0, -1);
            }
            $channelsCodes = explode("|", $channelsCodes);
            $channelsCodes = array_diff($channelsCodes, [$channel->getCode()]);
            $this->channelsCodes = sprintf('|%s|', implode('|', $channelsCodes));
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
        try {
            if ($this->getDefaultName()) {
                return (string) $this->getDefaultName();
            } else {
                return (string) $this->sku;
            }
        } catch (\LogicException $e) {
            return (string) $this->sku;
        }
    }

    /**
     * @return InventoryItem|null
     */
    public function getInventoryItem(): ?InventoryItem
    {
        return $this->inventoryItem;
    }

    /**
     * @param InventoryItem $item
     *
     * @return $this
     */
    public function setInventoryItem(InventoryItem $item)
    {
        $this->inventoryItem = $item;

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
     * @return ArrayCollection|ProductSupplierRelation[]
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
     * @return PriceListInterface|null
     */
    public function getSalesChannelPrice(SalesChannel $salesChannel)
    {
        /** @var AssembledChannelPriceList $productChannelPrice */
        $productChannelPrice = $this->getChannelPrices()
            ->filter(function ($productChannelPrice) use ($salesChannel) {
                /** @var AssembledChannelPriceList $productChannelPrice */
                return $productChannelPrice->getChannel() === $salesChannel;
            })
            ->first();

        if ($productChannelPrice) {
            return $productChannelPrice;
        }

        return $this->getPrice($salesChannel->getCurrency());
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
            if (!$category->hasProduct($this)) {
                $category->addProduct($this);
            }
            $this->addCategoryCode($category->getCode());
        }

        return $this;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function addCategoryCode($code)
    {
        if (strpos($this->categoriesCodes, '|') === false) {
            $categoriesCodes = [];
        } else {
            $categoriesCodes = $this->categoriesCodes;
            if (substr($categoriesCodes, 0, 1) === '|') {
                $categoriesCodes = substr($categoriesCodes, 1);
            }
            if (substr($categoriesCodes, -1, 1) === '|') {
                $categoriesCodes = substr($categoriesCodes, 0, -1);
            }
            $categoriesCodes = explode("|", $categoriesCodes);
        }
        if (!in_array($code, $categoriesCodes)) {
            $categoriesCodes[] = $code;
            $this->categoriesCodes = sprintf('|%s|', implode('|', $categoriesCodes));
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
            $categoriesCodes = $this->categoriesCodes;
            if (substr($categoriesCodes, 0, 1) === '|') {
                $categoriesCodes = substr($categoriesCodes, 1);
            }
            if (substr($categoriesCodes, -1, 1) === '|') {
                $categoriesCodes = substr($categoriesCodes, 0, -1);
            }
            $categoriesCodes = explode("|", $categoriesCodes);
            $categoriesCodes = array_diff($categoriesCodes, [$category->getCode()]);
            $this->categoriesCodes = sprintf('|%s|', implode('|', $categoriesCodes));
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

    /**
     * @return AttributeFamily
     */
    public function getAttributeFamily()
    {
        return $this->attributeFamily;
    }

    /**
     * @param AttributeFamily $attributeFamily
     * @return $this
     */
    public function setAttributeFamily(AttributeFamily $attributeFamily)
    {
        $this->attributeFamily = $attributeFamily;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Product
     */
    public function setType($type)
    {
        $this->type = $type;
        
        return $this;
    }

    /**
     * This field is read-only, updated automatically prior to persisting.
     *
     * @return string
     */
    public function getDenormalizedDefaultName()
    {
        return $this->denormalizedDefaultName;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (!$this->getDefaultName()) {
            throw new \RuntimeException(sprintf('Product %s has to have a default name', $this->getSku()));
        }
        $this->updateDenormalizedProperties();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        if (!$this->getDefaultName()) {
            throw new \RuntimeException(sprintf('Product %s has to have a default name', $this->getSku()));
        }
        $this->updateDenormalizedProperties();
    }

    public function updateDenormalizedProperties(): void
    {
        if (!$this->getDefaultName()) {
            throw new \RuntimeException(sprintf('Product %s has to have a default name', $this->getSku()));
        }
        $this->denormalizedDefaultName = $this->getDefaultName()->getString();
    }
}
