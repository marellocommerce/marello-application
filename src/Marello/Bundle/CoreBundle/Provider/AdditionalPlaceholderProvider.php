<?php

namespace Marello\Bundle\CoreBundle\Provider;

use Marello\Bundle\CoreBundle\Model\AdditionalPlaceholderDataInterface;

class AdditionalPlaceholderProvider
{
    /**
     * @var array
     */
    private $placeholderProviders = [];

    /**
     * @param AdditionalPlaceholderDataInterface $additionalPlaceholderData
     * @return $this
     */
    public function addAdditionalPlaceholderDataProvider(AdditionalPlaceholderDataInterface $additionalPlaceholderData)
    {
        $sections = $additionalPlaceholderData->getPlaceHolderSections();
        $name =  $additionalPlaceholderData->getName();
        foreach ($sections as $section) {
            if ($this->hasPlaceholderProvider($section, $name)) {
                throw new \LogicException(
                    sprintf('Placeholder provider with name "%s" already registered in section %s', $name, $section)
                );
            }

            $this->placeholderProviders[$section][$name] = $additionalPlaceholderData;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getPlaceholderProviders()
    {
        return $this->placeholderProviders;
    }

    /**
     * @param string $section
     * @param string $name
     * @return array
     */
    public function getPlaceHolderProvider($section, $name)
    {
        if ($this->hasPlaceholderProvider($section, $name)) {
            return $this->placeholderProviders[$section][$name];
        }

        return [];
    }

    /**
     * @param string $section
     * @return array
     */
    public function getPlaceHolderProvidersBySection($section)
    {
        if ($this->hasPlaceholderProviderSection($section)) {
            return $this->placeholderProviders[$section];
        }

        return [];
    }

    /**
     * @param string $section
     * @return bool
     */
    public function hasPlaceholderProviderSection($section)
    {
        return isset($this->placeholderProviders[$section]);
    }

    /**
     * @param string $section
     * @param string $name
     * @return boolean
     */
    public function hasPlaceholderProvider($section, $name)
    {
        return isset($this->placeholderProviders[$section][$name]);
    }
}
