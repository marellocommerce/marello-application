<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository")
 * @ORM\Table(name="marello_inventory_warehouse",
 *       uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"code"})
 *      }
 * )
 * @Oro\Config(
 *      defaultValues={
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "ownership"={
 *              "owner_type"="ORGANIZATION",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id"
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
 */
class Warehouse implements EmailHolderInterface, ExtendEntityInterface
{
    use ExtendEntityTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="label", nullable=false)
     *
     * @var string
     *
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $label;

    /**
     * @ORM\Column(type="string", name="code", nullable=false)
     *
     * @var string
     *
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $code;

    /**
     * @ORM\Column(type="boolean", nullable=false, name="is_default")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     * @var bool
     */
    protected $default;

    /**
     * @var OrganizationInterface
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $owner;

    /**
     * @var MarelloAddress
     *
     * @ORM\OneToOne(targetEntity="Marello\Bundle\AddressBundle\Entity\MarelloAddress", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=40,
     *              "full"=true,
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $address = null;

    /**
     * @var WarehouseType
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\WarehouseType")
     * @ORM\JoinColumn(name="warehouse_type", referencedColumnName="name")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=50,
     *              "full"=true,
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $warehouseType;
    
    /**
     * @ORM\ManyToOne(targetEntity="WarehouseGroup", inversedBy="warehouses")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=60,
     *              "full"=true,
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $group;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(name="notifier", type="string", nullable=true)
     *
     * @var string
     */
    protected $notifier;

    /**
     * @ORM\Column(name="sort_order_ood_loc", type="integer", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
                "entity"={
     *               "label"="marello.inventory.warehouse.sort_order_ood_loc.label"
     *           },
     *      }
     * )
     *
     * @var int
     */
    protected $sortOrderOodLoc;

    /**
     * @ORM\Column(name="order_on_demand_location", type="boolean", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var bool
     */
    protected $orderOnDemandLocation;

    /**
     * @param string $label
     * @param bool   $default
     */
    public function __construct($label = null, $default = false)
    {
        $this->label   = $label;
        $this->default = $default;
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
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @param boolean $default
     *
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return OrganizationInterface
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param OrganizationInterface $owner
     *
     * @return $this
     */
    public function setOwner(OrganizationInterface $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->label;
    }

    /**
     * @return MarelloAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param MarelloAddress $address
     *
     * @return $this
     */
    public function setAddress(MarelloAddress $address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return WarehouseType
     */
    public function getWarehouseType()
    {
        return $this->warehouseType;
    }

    /**
     * @param WarehouseType $warehouseType
     *
     * @return $this
     */
    public function setWarehouseType(WarehouseType $warehouseType)
    {
        $this->warehouseType = $warehouseType;

        return $this;
    }

    /**
     * @return WarehouseGroup|null
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param WarehouseGroup|null $group
     *
     * @return $this
     */
    public function setGroup(WarehouseGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Warehouse
     */
    public function setEmail($email = null): self
    {
        $this->email = $email;
        
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotifier(): ?string
    {
        return $this->notifier;
    }

    /**
     * @param string|null $notifier
     * @return $this
     */
    public function setNotifier(string $notifier = null): self
    {
        $this->notifier = $notifier;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSortOrderOodLoc(): ?int
    {
        return $this->sortOrderOodLoc;
    }

    /**
     * @param int|null $sortOrderOodLoc
     * @return $this
     */
    public function setSortOrderOodLoc(int $sortOrderOodLoc = null): self
    {
        $this->sortOrderOodLoc = $sortOrderOodLoc;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isOrderOnDemandLocation(): ?bool
    {
        return $this->orderOnDemandLocation;
    }

    /**
     * @param bool|null $orderOnDemandLocation
     * @return $this
     */
    public function setOrderOnDemandLocation(bool $orderOnDemandLocation = null): self
    {
        $this->orderOnDemandLocation = $orderOnDemandLocation;

        return $this;
    }
}
