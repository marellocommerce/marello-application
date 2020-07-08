<?php

namespace Marello\Bundle\Magento2Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\Magento2Bundle\Model\ExtendStore;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * @ORM\Entity
 * @ORM\Table(
 *  name="marello_m2_store",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unq_store_code_idx", columns={"code", "channel_id"})
 *  }
 * )
 * @Config()
 */
class Store extends ExtendStore implements OriginAwareInterface, IntegrationAwareInterface
{
    use IntegrationEntityTrait, NullableOriginTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=32, nullable=false)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var Website
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\Magento2Bundle\Entity\Website", inversedBy="stores")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $website;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean", options={"default"=false})
     */
    protected $isActive;

    /**
     * Represent locale uses on store view in ICU format
     *
     * @var string
     *
     * @ORM\Column(name="locale_id", type="string", length=255, nullable=true)
     */
    protected $localeId;

    /**
     * An internal presentation of localization will be guessed by "localeId"
     *
     * @var Localization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\LocaleBundle\Entity\Localization")
     * @ORM\JoinColumn(name="localization_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $localization;

    /**
     * @var string
     *
     * @ORM\Column(name="base_currency_code", type="string", length=3, nullable=true)
     */
    protected $baseCurrencyCode;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string|null $code
     *
     * @return Store
     */
    public function setCode(string $code = null): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $name
     *
     * @return Store
     */
    public function setName(string $name = null): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param Website $website
     *
     * @return Store
     */
    public function setWebsite(Website $website = null): self
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return Website
     */
    public function getWebsite(): ?Website
    {
        return $this->website;
    }

    /**
     * @return string
     */
    public function getWebsiteName(): ?string
    {
        return $this->website ? $this->website->getName() : null;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocalId(): ?string
    {
        return $this->localId;
    }

    /**
     * @param string|null $localId
     * @return $this
     */
    public function setLocalId(string $localId = null): self
    {
        $this->localId = $localId;

        return $this;
    }

    /**
     * @return Localization|null
     */
    public function getLocalization(): ?Localization
    {
        return $this->localization;
    }

    /**
     * @param Localization|null $localization
     * @return $this
     */
    public function setLocalization(Localization $localization = null): self
    {
        $this->localization = $localization;

        return $this;
    }


    /**
     * @param string|null $baseCurrencyCode
     * @return $this
     */
    public function setBaseCurrencyCode(string $baseCurrencyCode = null): self
    {
        $this->baseCurrencyCode = $baseCurrencyCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseCurrencyCode(): ?string
    {
        return $this->baseCurrencyCode;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName();
    }
}
