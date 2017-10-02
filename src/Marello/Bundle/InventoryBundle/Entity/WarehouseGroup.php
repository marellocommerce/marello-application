<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Marello\Bundle\InventoryBundle\Model\ExtendWarehouseGroup;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="marello_inventory_wh_group")
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *  defaultValues={
 *       "security"={
 *           "type"="ACL",
 *           "group_name"=""
 *       },
 *      "ownership"={
 *          "owner_type"="ORGANIZATION",
 *          "owner_field_name"="organization",
 *          "owner_column_name"="organization_id"
 *      }
 *  }
 * )
 */
class WarehouseGroup extends ExtendWarehouseGroup implements OrganizationAwareInterface
{
    use EntityCreatedUpdatedAtTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=10,
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=20
     *          }
     *      }
     * )
     */
    protected $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="system", type="boolean", nullable=false, options={"default"=false})
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=30
     *          }
     *      }
     *  )
     */
    protected $system = false;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @var Warehouse[]
     *
     * @ORM\OneToMany(targetEntity="Warehouse", mappedBy="group", fetch="EAGER")
     */
    protected $warehouses;

    /**
     * @var WarehouseChannelGroupLink
     *
     * @ORM\OneToOne(targetEntity="WarehouseChannelGroupLink", mappedBy="warehouseGroup")
     */
    protected $warehouseChannelGroupLink;

    public function __construct()
    {
        parent::__construct();

        $this->warehouses = new ArrayCollection();
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
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSystem()
    {
        return $this->system;
    }

    /**
     * @param boolean $system
     * @return $this
     */
    public function setSystem($system)
    {
        $this->system = $system;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * {@inheritdoc}
     * @return $this
     */
    public function setOrganization(OrganizationInterface $organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @return Collection|Warehouse[]
     */
    public function getWarehouses()
    {
        return $this->warehouses;
    }

    /**
     * @param Warehouse $warehouse
     * @return $this
     */
    public function addWarehouse(Warehouse $warehouse)
    {
        if (!$this->warehouses->contains($warehouse)) {
            $this->warehouses->add($warehouse);
        }

        return $this;
    }

    /**
     * @param Warehouse $warehouse
     * @return $this
     */
    public function removeWarehouse(Warehouse $warehouse)
    {
        if ($this->warehouses->contains($warehouse)) {
            $this->warehouses->removeElement($warehouse);
        }

        return $this;
    }

    /**
     * @return WarehouseChannelGroupLink
     */
    public function getWarehouseChannelGroupLink()
    {
        return $this->warehouseChannelGroupLink;
    }

    /**
     * @param WarehouseChannelGroupLink $warehouseChannelGroupLink
     * @return $this
     */
    public function setWarehouseChannelGroupLink($warehouseChannelGroupLink)
    {
        $this->warehouseChannelGroupLink = $warehouseChannelGroupLink;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
