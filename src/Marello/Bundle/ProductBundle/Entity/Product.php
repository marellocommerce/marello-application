<?php

namespace Marello\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

use Marello\Bundle\PricingBundle\Entity\ProductPrice;

use Marello\Bundle\ProductBundle\Model\ExtendProduct;

/**
 * Represents a Marello Product
 *
 * @ORM\Entity
 * @ORM\Table(
 *      name="marello_product_product",
 *      indexes={
 *          @ORM\Index(name="idx_marello_product_created_at", columns={"created_at"}),
 *          @ORM\Index(name="idx_marello_product_updated_at", columns={"updated_at"})
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *  routeName="marello_product_index",
 *  routeView="marello_product_view",
 *  defaultValues={
 *      "entity"={"icon"="icon-barcode"},
 *      "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="user_owner_id",
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
class Product extends ExtendProduct
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
     * @var integer
     *
     * @ORM\Column(name="stock_level", type="float", nullable=true)
     */
    protected $stockLevel;

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
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @var Collection|ProductPrice[]
     *
     * @ORM\OneToMany(targetEntity="Marello\Bundle\PricingBundle\Entity\ProductPrice", mappedBy="product", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $prices;

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
        $this->prices           = new ArrayCollection();
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
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return int
     */
    public function getStockLevel()
    {
        return $this->stockLevel;
    }

    /**
     * @param int $stockLevel
     * @return Product
     */
    public function setStockLevel($stockLevel)
    {
        $this->stockLevel = $stockLevel;

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
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     * @return Product
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
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

}
