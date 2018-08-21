<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Model\ExtendWarehouse;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
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
class Warehouse extends ExtendWarehouse
{
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
     * @param string $label
     * @param bool   $default
     */
    public function __construct($label = null, $default = false)
    {
        parent::__construct();
        
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
     * @return WarehouseGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param WarehouseGroup $group
     *
     * @return $this
     */
    public function setGroup(WarehouseGroup $group)
    {
        $this->group = $group;

        return $this;
    }
}
