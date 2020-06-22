<?php

namespace Marello\Bundle\LocaleBundle\Model;

use Oro\Bundle\LocaleBundle\Entity\Localization;

interface LocalizationAwareInterface
{
    /**
     * @return Localization
     */
    public function getLocalization();

    /**
     * @param Localization|null $localization
     * @return $this
     */
    public function setLocalization(Localization $localization = null);
}
