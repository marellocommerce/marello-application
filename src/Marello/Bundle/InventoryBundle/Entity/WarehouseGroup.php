<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseGroupRepository")
 * @ORM\Table(name="marello_inventory_wh_group")
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Config(
 *  defaultValues={
 *       "security"={
 *           "type"="ACL",
 *           "group_name"=""
 *       },
 *      "ownership"={
 *          "owner_type"="ORGANIZATION",
 *          "owner_field_name"="organization",
 *          "owner_column_name"="organization_id"
 *      },
 *      "dataaudit"={
 *          "auditable"=true
 *      }
 *  }
 * )
 */
class WarehouseGroup implements OrganizationAwareInterface, ExtendEntityInterface
{
    use EntityCreatedUpdatedAtTrait;
    use AuditableOrganizationAwareTrait;
    use ExtendEntityTrait;

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
     * @Oro\ConfigField(
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
     * @Oro\ConfigField(
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
     * @ORM\Column(name="is_system", type="boolean", nullable=false, options={"default"=false})
     * @Oro\ConfigField(
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
     * @var Warehouse[]
     *
     * @ORM\OneToMany(targetEntity="Warehouse", mappedBy="group", cascade={"persist"}, fetch="EAGER")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     *  )
     */
    protected $warehouses;

    /**
     * @var WarehouseChannelGroupLink
     *
     * @ORM\OneToOne(targetEntity="WarehouseChannelGroupLink", mappedBy="warehouseGroup")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     *  )
     */
    protected $warehouseChannelGroupLink;

    public function __construct()
    {
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
            $warehouse->setGroup($this);
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
