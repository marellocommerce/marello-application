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
     * @return Localization
     */
    public function getLocalization()
    {
        return $this->localization;
    }

    /**
     * @param Localization $localization
     * @return $this
     */
    public function setLocalization(Localization $localization = null)
    {
        $this->localization = $localization;

        return $this;
    }
}
