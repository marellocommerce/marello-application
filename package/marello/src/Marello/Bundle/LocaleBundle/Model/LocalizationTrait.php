<?php

namespace Marello\Bundle\LocaleBundle\Model;

use Oro\Bundle\LocaleBundle\Entity\Localization;

trait LocalizationTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\LocaleBundle\Entity\Localization")
     * @ORM\JoinColumn(name="localization_id", nullable=true)
     *
     * @var Localization
     */
    protected $localization;


    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=5, nullable=true)
     */
    protected $locale;

    /**
     * @return Localization
     */
    public function getLocalization()
    {
        return $this->localization;
    }

    /**
     * @return $this
     */
    public function setLocalization($localization)
    {
        $this->localization = $localization;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}
