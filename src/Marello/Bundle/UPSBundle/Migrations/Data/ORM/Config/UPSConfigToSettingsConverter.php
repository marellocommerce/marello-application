<?php

namespace Marello\Bundle\UPSBundle\Migrations\Data\ORM\Config;

use Marello\Bundle\UPSBundle\Entity\UPSSettings;

class UPSConfigToSettingsConverter
{
    /**
     * @param UPSConfig $config
     *
     * @return mixed
     */
    public function convert(UPSConfig $config)
    {
        $settings = new UPSSettings();

        $settings
            ->setUpsApiUser($config->getUser())
            ->setUpsApiPassword($config->getPassword())
            ->setUpsApiKey($config->getAccessLicenseKey())
            ->setUpsShippingAccountName($config->getShippingAccountName())
            ->setUpsShippingAccountNumber($config->getShippingAccountNumber())
            ->setUpsCountry($config->getCountry())
            ->setUpsPickupType($config->getPickupType())
            ->setUpsUnitOfWeight($config->getUnitOfWeight());


        $baseUrl = $config->getBaseUrl();
        
        return $settings;
    }
}
