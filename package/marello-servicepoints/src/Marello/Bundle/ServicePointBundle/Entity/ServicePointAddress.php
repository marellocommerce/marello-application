<?php

namespace Marello\Bundle\ServicePointBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\ServicePointBundle\Model\ExtendServicePointAddress;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="marello_sp_address")
 * @ORM\HasLifecycleCallbacks
 * @Config(
 *     defaultValues={
 *         "entity"={
 *             "label"="marello.servicepoint.servicepoint_address.entity_label",
 *             "plural_label"="marello.servicepoint.servicepoint_address.entity_plural_label"
 *         },
 *         "dataaudit"={
 *             "auditable"=true
 *         },
 *         "security"={
 *             "type"="ACL",
 *             "group_name"=""
 *         }
 *     }
 * )
 */
class ServicePointAddress extends ExtendServicePointAddress implements AddressInterface, DatesAwareInterface
{
    use DatesAwareTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "oro.address.id.label"
     *     }
     * })
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=500, nullable=true)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "oro.address.street.label"
     *     }
     * })
     */
    protected $street;

    /**
     * @var string
     *
     * @ORM\Column(name="street2", type="string", length=500, nullable=true)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "oro.address.street2.label"
     *     }
     * })
     */
    protected $street2;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "oro.address.city.label"
     *     }
     * })
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_code", type="string", length=255, nullable=true)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "oro.address.postal_code.label"
     *     }
     * })
     */
    protected $postalCode;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\Country")
     * @ORM\JoinColumn(name="country_code", referencedColumnName="iso2_code", nullable=false)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "oro.address.country.label"
     *     }
     * })
     */
    protected $country;

    /**
     * @var Region
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\Region")
     * @ORM\JoinColumn(name="region_code", referencedColumnName="combined_code", nullable=true)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "oro.address.region.label"
     *     }
     * })
     */
    protected $region;

    /**
     * @var string
     *
     * @ORM\Column(name="region_text", type="text", nullable=true)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "oro.address.region_text.label"
     *     }
     * })
     */
    protected $regionText;

    /**
     * @var string
     *
     * @ORM\Column(name="organization", type="string", length=255, nullable=true)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "oro.address.organization.label"
     *     }
     * })
     */
    protected $organization;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $street
     * @return $this
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street2
     * @return $this
     */
    public function setStreet2($street2)
    {
        $this->street2 = $street2;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet2()
    {
        return $this->street2;
    }

    /**
     * @param string $city
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param Region $region
     * @return $this
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return Region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @return string
     */
    public function getRegionText(): ?string
    {
        return $this->regionText;
    }

    /**
     * @param string $regionText
     * @return ServicePointAddress
     */
    public function setRegionText(?string $regionText): ServicePointAddress
    {
        $this->regionText = $regionText;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegionName()
    {
        return $this->getRegion() ? $this->getRegion()->getName() : (string)$this->regionText;
    }

    /**
     * @return string
     */
    public function getRegionCode()
    {
        return $this->getRegion() ? $this->getRegion()->getCode() : '';
    }

    /**
     * @param string $postalCode
     * @return $this
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param Country $country
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getCountryName()
    {
        return $this->getCountry() ? $this->getCountry()->getName() : '';
    }

    /**
     * Get country ISO3 code
     *
     * @return string
     */
    public function getCountryIso3()
    {
        return $this->getCountry() ? $this->getCountry()->getIso3Code() : '';
    }

    /**
     * @return string
     */
    public function getCountryIso2()
    {
        return $this->getCountry() ? $this->getCountry()->getIso2Code() : '';
    }

    /**
     * @param string $organization
     * @return $this
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $data = [
            $this->getStreet(),
            $this->getStreet2(),
            $this->getCity(),
            $this->getRegionName(),
            ',',
            $this->getCountry(),
            $this->getPostalCode(),
        ];

        return trim(implode(' ', $data), " \t\n\r\0\x0B,");
    }
}
