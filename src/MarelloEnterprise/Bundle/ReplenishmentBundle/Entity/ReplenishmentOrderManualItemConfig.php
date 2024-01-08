<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="marello_repl_order_m_item_config")
 * @ORM\HasLifecycleCallbacks
 * @Oro\Config(
 *      defaultValues={
 *          "dataaudit"={
 *              "auditable"=false
 *          }
 *      }
 * )
 */
class ReplenishmentOrderManualItemConfig implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\Warehouse")
     * @ORM\JoinColumn(name="origin_id", nullable=true, onDelete="SET NULL")
     *
     * @var Warehouse
     */
    protected $origin;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\Warehouse")
     * @ORM\JoinColumn(name="destination_id", nullable=true, onDelete="SET NULL")
     *
     * @var Warehouse
     */
    protected $destination;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\Product")
     * @ORM\JoinColumn(name="product_id", nullable=true, onDelete="SET NULL")
     *
     * @var Product
     */
    protected $product;

    /**
     * @ORM\Column(name="quantity", type="integer", nullable=true)
     *
     * @var int
     */
    protected $quantity;

    /**
     * @ORM\Column(name="all_quantity", type="boolean")
     *
     * @var boolean
     */
    protected $allQuantity = false;

    /**
     * @ORM\Column(name="available_quantity", type="integer", nullable=true)
     *
     * @var int
     */
    protected $availableQuantity;

    /**
     * @ORM\ManyToOne(targetEntity="ReplenishmentOrderConfig", inversedBy="manualItems")
     * @ORM\JoinColumn(name="order_config_id", nullable=false, onDelete="CASCADE")
     *
     * @var ReplenishmentOrderConfig
     */
    protected $orderConfig;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Warehouse|null
     */
    public function getOrigin(): ?Warehouse
    {
        return $this->origin;
    }

    /**
     * @param Warehouse $origin
     * @return self
     */
    public function setOrigin(Warehouse $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * @return Warehouse|null
     */
    public function getDestination(): ?Warehouse
    {
        return $this->destination;
    }

    /**
     * @param Warehouse $destination
     * @return self
     */
    public function setDestination(Warehouse $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     * @return self
     */
    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /**
     * @param int|null $quantity
     * @return self
     */
    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllQuantity(): bool
    {
        return $this->allQuantity;
    }

    /**
     * @param bool $allQuantity
     * @return self
     */
    public function setAllQuantity(bool $allQuantity): self
    {
        $this->allQuantity = $allQuantity;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAvailableQuantity(): ?int
    {
        return $this->availableQuantity;
    }

    /**
     * @param int|null $availableQuantity
     * @return self
     */
    public function setAvailableQuantity(?int $availableQuantity): self
    {
        $this->availableQuantity = $availableQuantity;

        return $this;
    }

    /**
     * @return ReplenishmentOrderConfig|null
     */
    public function getOrderConfig(): ?ReplenishmentOrderConfig
    {
        return $this->orderConfig;
    }

    /**
     * @param ReplenishmentOrderConfig $orderConfig
     * @return self
     */
    public function setOrderConfig(ReplenishmentOrderConfig $orderConfig): self
    {
        $this->orderConfig = $orderConfig;

        return $this;
    }
}
