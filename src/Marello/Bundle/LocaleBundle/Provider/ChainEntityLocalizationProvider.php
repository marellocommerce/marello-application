<?php

namespace Marello\Bundle\LocaleBundle\Provider;

use Marello\Bundle\LocaleBundle\Model\LocalizationAwareInterface;

class ChainEntityLocalizationProvider implements EntityLocalizationProviderInterface
{
    /**
     * @var EntityLocalizationProviderInterface[]
     */
    private $providers = [];
    
    public function addProvider(EntityLocalizationProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * @inheritDoc
     */
    public function getLocalization(LocalizationAwareInterface $entity)
    {
        $localization = null;
        foreach ($this->providers as $provider) {
            if ($provider->isApplicable($entity)) {
                $localization = $provider->getLocalization($entity);
                if ($localization) {
                    return $localization;
                }
            }
        }
        
        return $localization;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(LocalizationAwareInterface $entity)
    {
        return true;
    }
}
