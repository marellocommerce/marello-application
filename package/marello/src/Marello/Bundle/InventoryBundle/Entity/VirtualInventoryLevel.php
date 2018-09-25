<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Marello\Bundle\InventoryBundle\Model\VirtualInventoryLevelInterface;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\InventoryBundle\Entity\Repository\VirtualInventoryRepository")
 * @ORM\Table(name="marello_vrtl_inventory_level",
 *       uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"product_id", "channel_group_id"})
 *      }
 * )
 * @Oro\Config(
 *      defaultValues={
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "entity"={
 *              "icon"="fa-list-alt"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "ownership"={
 *              "owner_type"="ORGANIZATION",
 *              "owner_field_name"="organization",
 *              "owner_column_name"="organization_id"
 *          }
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class VirtualInventoryLevel implements OrganizationAwareInterface, VirtualInventoryLevelInterface
{
    use EntityCreatedUpdatedAtTrait;
    use AuditableOrganizationAwareTrait;

    /**
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
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="marello.product.entity_label"
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var ProductInterface
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\SalesBundle\Entity\SalesChannelGroup")
     * @ORM\JoinColumn(name="channel_group_id", referencedColumnName="id", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="marello.sales.saleschannelgroup.entity_label"
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var SalesChannelGroup
     */
    protected $salesChannelGroup;

    /**
     * @ORM\Column(name="inventory_qty", type="integer", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="marello.inventory.virtualinventorylevel.inventory.label"
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var int
     */
    protected $inventory;

    /**
     * @ORM\Column(name="blncd_inventory_qty", type="integer", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="marello.inventory.virtualinventorylevel.balanced_inventory_qty.label"
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var int
     */
    protected $balancedInventory;


    /**
     * @ORM\Column(name="reserved_inventory_qty", type="integer", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="marello.inventory.virtualinventorylevel.reserved_inventory_qty.label"
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var int
     */
    protected $reservedInventory;

    /**
     * VirtualInventoryLevel constructor.
     * @param ProductInterface $product
     * @param SalesChannelGroup $group
     * @param null $inventory
     */
    public function __construct(ProductInterface $product, SalesChannelGroup $group, $inventory = null)
    {
        $this->product = $product;
        $this->salesChannelGroup = $group;
        $this->inventory = $this->balancedInventory = $inventory;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param ProductInterface $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return SalesChannelGroup
     */
    public function getSalesChannelGroup()
    {
        return $this->salesChannelGroup;
    }

    /**
     * @param SalesChannelGroup $salesChannelGroup
     * @return $this
     */
    public function setSalesChannelGroup(SalesChannelGroup $salesChannelGroup)
    {
        $this->salesChannelGroup = $salesChannelGroup;

        return $this;
    }

    /**
     * @return int
     */
    public function getInventoryQty()
    {
        return $this->inventory;
    }

    /**
     * @param int $inventory
     * @return $this
     */
    public function setInventoryQty($inventory)
    {
        $this->inventory = $inventory;

        return $this;
    }

    /**
     * @return int
     */
    public function getReservedInventoryQty()
    {
        return $this->reservedInventory;
    }

    /**
     * @param int $reservedInventory
     * @return $this
     */
    public function setReservedInventoryQty($reservedInventory)
    {
        $this->reservedInventory = $reservedInventory;

        return $this;
    }

    /**
     * @return int
     */
    public function getBalancedInventoryQty()
    {
        return $this->balancedInventory;
    }

    /**
     * @param $balancedInventory
     * @return $this
     */
    public function setBalancedInventoryQty($balancedInventory)
    {
        $this->balancedInventory = $balancedInventory;

        return $this;
    }
}
