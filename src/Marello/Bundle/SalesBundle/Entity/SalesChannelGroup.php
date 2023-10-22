<?php

namespace Marello\Bundle\SalesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelGroupRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(
 *     name="marello_sales_channel_group",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="UNIQ_759DCFAB3D6A9E29",
 *              columns={"integration_channel_id"}
 *          )
 *      }
 * )
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
class SalesChannelGroup implements ExtendEntityInterface
{
    use EntityCreatedUpdatedAtTrait;
    use AuditableOrganizationAwareTrait;
    use ExtendEntityTrait;
    
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
     * @ORM\Column(name="is_system", type="boolean", nullable=false, options={"default"=false})
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

    /**
     * @ORM\OneToOne(targetEntity="Oro\Bundle\IntegrationBundle\Entity\Channel")
     * @ORM\JoinColumn(name="integration_channel_id", nullable=true, onDelete="SET NULL", unique=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var Channel
     */
    protected $integrationChannel;

    public function __construct()
    {
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
     * @return Channel|null
     */
    public function getIntegrationChannel()
    {
        return $this->integrationChannel;
    }

    /**
     * @param Channel|null $integrationChannel
     * @return $this
     */
    public function setIntegrationChannel(Channel $integrationChannel = null)
    {
        $this->integrationChannel = $integrationChannel;

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
