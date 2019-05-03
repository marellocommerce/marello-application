<?php

namespace Marello\Bundle\LocaleBundle\Provider;

use Marello\Bundle\LocaleBundle\Model\LocaleAwareInterface;
use Oro\Bundle\LocaleBundle\Entity\Localization;

interface EntityLocalizationProviderInterface
{
    /**
     * @param LocaleAwareInterface $entity
     * @return Localization|null
     */
    public function getLocalization(LocaleAwareInterface $entity);

    /**
     * @param LocaleAwareInterface $entity
     * @return boolean
     */
    public function isApplicable(LocaleAwareInterface $entity);
}
