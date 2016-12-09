<?php

namespace Marello\Bundle\SalesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

use Marello\Bundle\PricingBundle\Model\CurrencyAwareInterface;
use Marello\Bundle\SalesBundle\Model\ExtendSalesChannel;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
 *  defaultValues={
 *      "entity"={"icon"="icon-sitemap"},
 *      "ownership"={
 *          "owner_type"="ORGANIZATION",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class SalesChannel extends ExtendSalesChannel implements CurrencyAwareInterface
{
    use EntityCreatedUpdatedAtTrait;
    
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
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $code;

    /**
     * @var string
     * @ORM\Column(name="currency", type="string", length=5, nullable=false)
     */
    protected $currency;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $active = true;

    /**
     * @var boolean
     * mark a channel as a default channel
     * @ORM\Column(name="is_default", type="boolean", nullable=false)
     */
    protected $default = true;

    /**
     * @var OrganizationInterface
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $owner;

    /**
     * Channel type is by default marello. It means that api is used to push data into marello itself. No integration
     * is used to pull data from any other source.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $channelType = self::DEFAULT_TYPE;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\LocaleBundle\Entity\Localization")
     * @ORM\JoinColumn(name="default_language_id", referencedColumnName="id")
     *
     * @var Localization
     */
    protected $defaultLanguage;

    /**
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\LocaleBundle\Entity\Localization")
     * @ORM\JoinTable(name="marello_sales_channel_lang",
     *     joinColumns={@ORM\JoinColumn(name="sales_channel_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="localization_id", referencedColumnName="id", unique=true)}
     * )
     * @var ArrayCollection
     */
    protected $supportedLanguages;

    /**
     * @param string|null $name
     */
    public function __construct($name = null)
    {
        $this->name = $name;
        $this->supportedLanguages = new ArrayCollection();
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
     * Set defaultLanguage
     *
     * @param Localization $defaultLanguage
     *
     * @return SalesChannel
     */
    public function setDefaultLanguage(Localization $defaultLanguage = null)
    {
        $this->defaultLanguage = $defaultLanguage;

        return $this;
    }

    /**
     * Get defaultLanguage
     *
     * @return Localization
     */
    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }

    /**
     * Add supportedLanguage
     *
     * @param Localization $supportedLanguage
     *
     * @return SalesChannel
     */
    public function addSupportedLanguage(Localization $supportedLanguage)
    {
        $this->supportedLanguages[] = $supportedLanguage;

        return $this;
    }

    /**
     * Remove supportedLanguage
     *
     * @param Localization $supportedLanguage
     */
    public function removeSupportedLanguage(Localization $supportedLanguage)
    {
        $this->supportedLanguages->removeElement($supportedLanguage);
    }

    /**
     * Get supportedLanguages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSupportedLanguages()
    {
        return $this->supportedLanguages;
    }

    /**
     * @Assert\Callback
     *
     * @param ExecutionContextInterface $context
     */
    public function validateSupportedLanguages(ExecutionContextInterface $context)
    {
        $found = false;
        foreach ($this->getSupportedLanguages() as $lang) {
            if ($lang === $this->getDefaultLanguage()) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $this->addSupportedLanguage($this->getDefaultLanguage());
        }
    }
}
