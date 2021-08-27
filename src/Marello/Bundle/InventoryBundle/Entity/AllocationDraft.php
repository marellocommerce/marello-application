<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Elasticsearch\Endpoints\Cat\Allocation;
use Marello\Bundle\InventoryBundle\Model\ExtendAllocationDraft;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\Validator\Constraints as Assert;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Marello\Bundle\CoreBundle\DerivedProperty\DerivedPropertyAwareInterface;

/**
 * @ORM\Entity()
 * @Oro\Config(
 *      defaultValues={
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
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
 * @ORM\Table(name="marello_inventory_alloc_draft")
 * @ORM\HasLifecycleCallbacks()
 */
class AllocationDraft extends ExtendAllocationDraft implements
    DerivedPropertyAwareInterface,
    OrganizationAwareInterface
{
    use EntityCreatedUpdatedAtTrait;
    use AuditableOrganizationAwareTrait;
    
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var Collection|AllocationDraftItem[]
     *
     * @ORM\OneToMany(targetEntity="Marello\Bundle\InventoryBundle\Entity\AllocationDraftItem", mappedBy="allocationDraft", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     */
    protected $items;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\OrderBundle\Entity\Order", cascade={"persist"})
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $order;

    /**
     * @var MarelloAddress
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\AddressBundle\Entity\MarelloAddress", cascade={"persist"})
     * @ORM\JoinColumn(name="shipping_address_id", referencedColumnName="id")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $shippingAddress;

    /**
     * @var Warehouse
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\Warehouse")
     * @ORM\JoinColumn(name="warehouse_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $warehouse;

    /**
     * @var AllocationDraft
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\AllocationDraft", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "header"="Parent",
     *              "order"=30
     *          }
     *      }
     * )
     */
    protected $parent;

    /**
     * @var Collection|AllocationDraft[]
     *
     * @ORM\OneToMany(targetEntity="Marello\Bundle\InventoryBundle\Entity\AllocationDraft", mappedBy="parent")
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
    protected $children;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var string
     */
    protected $comment;

    /**
     * @ORM\Column(name="allocation_draft_number", type="string", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var string
     */
    protected $allocationDraftNumber;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", nullable=true)
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
     * @var string
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=false
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $status;

    public function __construct()
    {
        parent::__construct();
        
        $this->items = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Collection|AllocationDraftItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param AllocationDraftItem $item
     *
     * @return $this
     */
    public function addItem(AllocationDraftItem $item)
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setAllocationDraft($this);
        }

        return $this;
    }

    /**
     * @param AllocationDraftItem $item
     *
     * @return $this
     */
    public function removeItem(AllocationDraftItem $item)
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
        }

        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     *
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return MarelloAddress
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @param MarelloAddress $shippingAddress
     *
     * @return $this
     */
    public function setShippingAddress(MarelloAddress $shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * @return Warehouse|null
     */
    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    /**
     * @param Warehouse|null $warehouse
     * @return $this
     */
    public function setWarehouse(Warehouse $warehouse = null): self
    {
        $this->warehouse = $warehouse;
        
        return $this;
    }

    /**
     * @param AllocationDraft|null $parent
     * @return $this
     */
    public function setParent(AllocationDraft $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return AllocationDraft
     */
    public function getParent(): ?AllocationDraft
    {
        return $this->parent;
    }

    /**
     * @param AllocationDraft $child
     * @return $this
     */
    public function addChild(AllocationDraft $child)
    {
        if (!$this->hasChild($child)) {
            $child->setParent($this);
            $this->children->add($child);
        }

        return $this;
    }

    /**
     * @param AllocationDraft $child
     *
     * @return $this
     */
    public function removeChild(AllocationDraft $child)
    {
        if ($this->hasChild($child)) {
            $child->setParent(null);
            $this->children->removeElement($child);
        }

        return $this;
    }

    /**
     * @return Collection|AllocationDraft[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param AllocationDraft $child
     *
     * @return bool
     */
    protected function hasChild(AllocationDraft $child)
    {
        return $this->children->contains($child);
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAllocationDraftNumber()
    {
        return $this->allocationDraftNumber;
    }

    /**
     * @param $allocationDraftNumber
     * @return $this
     */
    public function setAllocationDraftNumber($allocationDraftNumber)
    {
        $this->allocationDraftNumber = $allocationDraftNumber;

        return $this;
    }

    /**
     * @param $id
     */
    public function setDerivedProperty($id)
    {
        if (!$this->allocationDraftNumber) {
            $this->setAllocationDraftNumber(sprintf('%09d', $id));
        }
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('#%s', $this->allocationDraftNumber);
    }
}
