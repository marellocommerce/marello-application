<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

use Marello\Bundle\InventoryBundle\Model\ExtendWarehouse;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository")
 * @ORM\Table(name="marello_inventory_warehouse")
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
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    protected $label;

    /**
     * @ORM\Column(type="boolean", nullable=false, name="is_default")
     *
     * @var bool
     */
    protected $default;

    /**
     * @var OrganizationInterface
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $owner;

    /**
     * @var MarelloAddress
     *
     * @ORM\OneToOne(targetEntity="Marello\Bundle\AddressBundle\Entity\MarelloAddress", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
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
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $warehouseType;

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
    public function setOwner($owner)
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
}
