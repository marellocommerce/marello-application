<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Marello\Bundle\InventoryBundle\Model\ExtendWarehouseChannelGroupLink;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="marello_inventory_wh_chg_link")
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *  defaultValues={
 *      "ownership"={
 *          "owner_type"="ORGANIZATION",
 *          "owner_field_name"="organization",
 *          "owner_column_name"="organization_id"
 *      }
 *  }
 * )
 */
class WarehouseChannelGroupLink extends ExtendWarehouseChannelGroupLink implements OrganizationAwareInterface
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
     * @var bool
     *
     * @ORM\Column(name="system", type="boolean", nullable=false, options={"default"=false})
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
     * @var WarehouseGroup
     *
     * @ORM\OneToOne(targetEntity="WarehouseGroup", inversedBy="warehouseChannelGroupLink")
     * @ORM\JoinColumn(name="warehouse_group_id", referencedColumnName="id")
     */
    protected $warehouseGroup;

    /**
     * @var SalesChannelGroup[]
     *
     * @ORM\ManyToMany(targetEntity="Marello\Bundle\SalesBundle\Entity\SalesChannelGroup", fetch="EAGER")
     * @ORM\JoinTable(name="marello_inventory_lnk_join_chg",
     *      joinColumns={@ORM\JoinColumn(name="link_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="channel_group_id", referencedColumnName="id", unique=true)}
     *      )
     */
    protected $salesChannelGroups;

    public function __construct()
    {
        parent::__construct();

        $this->salesChannelGroups = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return WarehouseGroup
     */
    public function getWarehouseGroup()
    {
        return $this->warehouseGroup;
    }

    /**
     * @param WarehouseGroup $warehouseGroup
     * @return $this
     */
    public function setWarehouseGroup(WarehouseGroup $warehouseGroup)
    {
        $this->warehouseGroup = $warehouseGroup;

        return $this;
    }

    /**
     * @return Collection|SalesChannelGroup[]
     */
    public function getSalesChannelGroups()
    {
        return $this->salesChannelGroups;
    }

    /**
     * @param SalesChannelGroup $salesChannelGroup
     * @return $this
     */
    public function addSalesChannelGroup(SalesChannelGroup $salesChannelGroup)
    {
        if (!$this->salesChannelGroups->contains($salesChannelGroup)) {
            $this->salesChannelGroups->add($salesChannelGroup);
        }

        return $this;
    }

    /**
     * @param SalesChannelGroup $salesChannelGroup
     * @return $this
     */
    public function removeSalesChannelGroup(SalesChannelGroup $salesChannelGroup)
    {
        if ($this->salesChannelGroups->contains($salesChannelGroup)) {
            $this->salesChannelGroups->removeElement($salesChannelGroup);
        }

        return $this;
    }
}
