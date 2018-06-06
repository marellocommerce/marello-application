<?php

namespace Marello\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * Represents a Marello Variant Product
 *
 * @ORM\Entity()
 * @ORM\Table(name="marello_product_variant")
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Config(
 *  routeName="marello_product_index",
 *  routeView="marello_product_view",
 *  defaultValues={
 *      "entity"={"icon"="fa-barcode"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      },
 *      "dataaudit"={
 *            "auditable"=true
 *      }
 *  }
 * )
 */
class Variant
{
    use EntityCreatedUpdatedAtTrait;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="variant_code", type="string", nullable=true, unique=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $variantCode;

    /**
     * @see \Marello\Bundle\InventoryBundle\Form\Type\ProductInventoryType
     *
     * @var Collection|Product[] $products
     *
     * @ORM\OneToMany(targetEntity="Product", cascade={"persist"}, mappedBy="variant")
     * @ORM\JoinTable(name="marello_product_to_variant")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $products;

    /**
     * Variant constructor.
     */
    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
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
    public function getVariantCode()
    {
        return $this->variantCode;
    }

    /**
     * @param string $variantCode
     */
    public function setVariantCode($variantCode)
    {
        $this->variantCode = $variantCode;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Add item
     *
     * @param Product $item
     *
     * @return Variant
     */
    public function addProduct(Product $item)
    {
        if (!$this->products->contains($item)) {
            $this->products->add($item);
            $item->setVariant($this);
        }

        return $this;
    }

    /**
     * Remove item
     *
     * @param Product $item
     *
     * @return Variant
     */
    public function removeProduct(Product $item)
    {
        if ($this->products->contains($item)) {
            $this->products->removeElement($item);
            $item->setVariant(null);
        }

        return $this;
    }
}
