<?php

namespace Marello\Bundle\LocaleBundle\Provider;

use Marello\Bundle\LocaleBundle\Model\LocalizationAwareInterface;
use Oro\Bundle\LocaleBundle\Entity\Localization;

interface EntityLocalizationProviderInterface
{
    /**
     * @param LocalizationAwareInterface $entity
     * @return Localization|null
     */
    public function getLocalization(LocalizationAwareInterface $entity);

    /**
     * @param LocalizationAwareInterface $entity
     * @return boolean
     */
    public function isApplicable(LocalizationAwareInterface $entity);
}
