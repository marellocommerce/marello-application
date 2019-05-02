<?php

namespace Marello\Bundle\SalesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Marello\Bundle\SalesBundle\Model\ExtendSalesChannelGroup;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelGroupRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="marello_sales_channel_group")
 * @Config(
 *  routeName="marello_sales_saleschannelgroup_index",
 *  routeView="marello_sales_saleschannelgroup_view",
 *  routeCreate="marello_sales_saleschannelgroup_create",
 *  routeUpdate="marello_sales_saleschannelgroup_update",
 *  defaultValues={
 *      "ownership"={
 *          "owner_type"="ORGANIZATION",
 *          "owner_field_name"="organization",
 *          "owner_column_name"="organization_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      },
 *      "dataaudit"={
 *          "auditable"=true
 *      }
 *  }
 * )
 */
class SalesChannelGroup extends ExtendSalesChannelGroup
{
    use EntityCreatedUpdatedAtTrait;
    use AuditableOrganizationAwareTrait;
    
    /**
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
     * @var SalesChannel[]
     *
     * @ORM\OneToMany(targetEntity="SalesChannel", mappedBy="group", cascade={"persist"}, fetch="EAGER")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     *  )
     */
    protected $salesChannels;

    public function __construct()
    {
        parent::__construct();
        
        $this->salesChannels = new ArrayCollection();
    }

    /**
     * @return mixed
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
     * @return Collection|SalesChannel[]
     */
    public function getSalesChannels()
    {
        return $this->salesChannels;
    }

    /**
     * @param SalesChannel $salesChannel
     *
     * @return $this
     */
    public function addSalesChannel(SalesChannel $salesChannel)
    {
        if (!$this->salesChannels->contains($salesChannel)) {
            $salesChannel->setGroup($this);
            $this->salesChannels->add($salesChannel);
        }

        return $this;
    }

    /**
     * @param SalesChannel $salesChannel
     *
     * @return $this
     */
    public function removeSalesChannel(SalesChannel $salesChannel)
    {
        if ($this->salesChannels->contains($salesChannel)) {
            $this->salesChannels->removeElement($salesChannel);
        }

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
