<?php

namespace Marello\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Marello\Bundle\LocaleBundle\Model\LocaleAwareInterface;
use Marello\Bundle\LocaleBundle\Model\LocalizationTrait;
use Marello\Bundle\PricingBundle\Model\CurrencyAwareInterface;
use Marello\Bundle\SalesBundle\Model\ExtendSalesChannel;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="marello_sales_sales_channel",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="marello_sales_sales_channel_codeidx",
 *              columns={"code"}
 *          )
 *      }
 * )
 * @Oro\Config(
 *  routeName="marello_sales_saleschannel_index",
 *  routeView="marello_sales_saleschannel_view",
 *  routeCreate="marello_sales_saleschannel_create",
 *  routeUpdate="marello_sales_saleschannel_update",
 *  defaultValues={
 *      "entity"={"icon"="fa-sitemap"},
 *      "ownership"={
 *          "owner_type"="ORGANIZATION",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="owner_id"
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
class SalesChannel extends ExtendSalesChannel implements
    CurrencyAwareInterface,
    LocaleAwareInterface
{
    use EntityCreatedUpdatedAtTrait;
    use LocalizationTrait;
    
    const DEFAULT_TYPE = 'marello';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
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
     * @var string
     * @ORM\Column(name="currency", type="string", length=5, nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $currency;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $active = true;

    /**
     * @var boolean
     * mark a channel as a default channel
     * @ORM\Column(name="is_default", type="boolean", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $default = true;

    /**
     * @var OrganizationInterface
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $owner;

    /**
     * Channel type is by default marello. It means that api is used to push data into marello itself. No integration
     * is used to pull data from any other source.
     *
     * @var string
     *
     * @ORM\Column(name="channel_type", type="string")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $channelType = self::DEFAULT_TYPE;
    
    /**
     * @var SalesChannelGroup
     *
     * @ORM\ManyToOne(targetEntity="SalesChannelGroup", inversedBy="salesChannels")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $group;

    /**
     * @ORM\OneToOne(targetEntity="Oro\Bundle\IntegrationBundle\Entity\Channel")
     * @ORM\JoinColumn(name="integration_channel_id", nullable=true)
     * @Oro\ConfigField(
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

    /**
     * @param string|null $name
     */
    public function __construct($name = null)
    {
        parent::__construct();
        
        $this->name = $name;
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
     *
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
    public function __toString()
    {
        return $this->name;
    }
    
    /**
     * @return string
     */
    public function getChannelType()
    {
        return $this->channelType;
    }

    /**
     * @param string $channelType
     *
     * @return $this
     */
    public function setChannelType($channelType)
    {
        $this->channelType = $channelType;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     *
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getDefault()
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
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

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
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @return SalesChannelGroup $group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param SalesChannelGroup $group
     * @return $this
     */
    public function setGroup(SalesChannelGroup $group)
    {
        $this->group = $group;

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
     * @param Channel $integrationChannel
     * @return $this
     */
    public function setIntegrationChannel(Channel $integrationChannel)
    {
        $this->integrationChannel = $integrationChannel;

        return $this;
    }
}
