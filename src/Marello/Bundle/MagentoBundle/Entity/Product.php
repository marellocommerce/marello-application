<?php

namespace Marello\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Marello\Bundle\MagentoBundle\Model\ExtendProduct;

/**
 * Class Product
 *
 * @package Marello\Bundle\MarelloMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="marello_magento_product")
 * @Config(
 *      defaultValues={
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="sales_data"
 *          },
 *          "note"={
 *              "immutable"=true
 *          },
 *          "activity"={
 *              "immutable"=true
 *          },
 *          "attachment"={
 *              "immutable"=true
 *          }
 *      }
 * )
 */
class Product extends ExtendProduct implements IntegrationAwareInterface, OriginAwareInterface
{
    use IntegrationEntityTrait;
    use OriginTrait;

    /*
     * FIELDS are duplicated to enable dataaudit only for customer fields
     */
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="sku", type="string", length=255, nullable=true)
     */
    protected $sku;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    protected $type;

    /**
     * @var double
     *
     * @ORM\Column(name="special_price", type="money", nullable=true)
     */
    protected $specialPrice;

    /**
     * @var double
     *
     * @ORM\Column(name="price", type="money", nullable=true)
     */
    protected $price;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(type="datetime", name="created_at")
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
     * @ORM\Column(type="datetime", name="updated_at")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    /**
     * @var Website[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Marello\Bundle\MagentoBundle\Entity\Website")
     * @ORM\JoinTable(name="marello_mage_prod_to_website",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="website_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $websites;

    public function __construct()
    {
        parent::__construct();

        $this->websites = new ArrayCollection();
        $this->prePersist();
    }

    /**
     * @param float $specialPrice
     *
     * @return Product
     */
    public function setSpecialPrice($specialPrice)
    {
        $this->specialPrice = $specialPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getSpecialPrice()
    {
        return $this->specialPrice;
    }

    /**
     * @param Website $website
     *
     * @return Product
     */
    public function addWebsite(Website $website)
    {
        if (!$this->websites->contains($website)) {
            $this->websites->add($website);
        }

        return $this;
    }

    /**
     * @param Website $website
     *
     * @return Product
     */
    public function removeWebsite(Website $website)
    {
        if ($this->websites->contains($website)) {
            $this->websites->remove($website);
        }

        return $this;
    }

    /**
     * @param Website[] $websites
     *
     * @return Product
     */
    public function setWebsites(array $websites)
    {
        $this->websites = new ArrayCollection($websites);

        return $this;
    }

    /**
     * @return Website[]
     */
    public function getWebsites()
    {
        return $this->websites;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getName();
    }

    /**
     * Pre update event handler
     *
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Pre persist event handler
     *
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
