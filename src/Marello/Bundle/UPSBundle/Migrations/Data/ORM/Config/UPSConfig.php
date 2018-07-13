<?php

namespace Marello\Bundle\UPSBundle\Migrations\Data\ORM\Config;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class UPSConfig
{
    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var UPSConfigKeysProvider
     */
    private $keysProvider;

    /**
     * @param ConfigManager $configManager
     * @param UPSConfigKeysProvider $keysProvider
     */
    public function __construct(
        ConfigManager $configManager,
        UPSConfigKeysProvider $keysProvider
    ) {
        $this->configManager = $configManager;
        $this->keysProvider = $keysProvider;
    }

    /**
     * @return null|string
     */
    public function getUser()
    {
        return $this->getConfigValue($this->keysProvider->getUserKey());
    }

    /**
     * @return null|string
     */
    public function getPassword()
    {
        return $this->getConfigValue($this->keysProvider->getPasswordKey());
    }

    /**
     * @return null|string
     */
    public function getAccessLicenseKey()
    {
        return $this->getConfigValue($this->keysProvider->getLisenceKey());
    }

    /**
     * @return null|string
     */
    public function getShippingAccountName()
    {
        return $this->getConfigValue($this->keysProvider->getShippingAccountNameKey());
    }

    /**
     * @return null|string
     */
    public function getShippingAccountNumber()
    {
        return $this->getConfigValue($this->keysProvider->getShippingAccountNumberKey());
    }

    /**
     * @return null|string
     */
    public function getPickupType()
    {
        return $this->getConfigValue($this->keysProvider->getPickupTypeKey());
    }

    /**
     * @return null|string
     */
    public function getCountry()
    {
        return $this->getConfigValue($this->keysProvider->getCountryKey());
    }

    /**
     * @return null|string
     */
    public function getUnitOfWeight()
    {
        return $this->getConfigValue($this->keysProvider->getUnitOfWeightKey());
    }

    /**
     * @return null|string
     */
    public function getBaseUrl()
    {
        return $this->getConfigValue($this->keysProvider->getBaseUrlKey());
    }

    /**
     * @return bool
     */
    public function isAllRequiredFieldsSet()
    {
        $fields = [
            $this->getUser(),
            $this->getPassword(),
            $this->getAccessLicenseKey(),
            $this->getShippingAccountName(),
            $this->getShippingAccountNumber(),
            $this->getPickupType(),
            $this->getCountry(),
            $this->getUnitOfWeight(),
            $this->getBaseUrl(),
        ];

        foreach ($fields as $field) {
            if ($field === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    private function getConfigValue($key)
    {
        return $this->configManager->get($this->getFullConfigKey($key));
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function getFullConfigKey($key)
    {
        return 'marello_shipping' . ConfigManager::SECTION_MODEL_SEPARATOR . $key;
    }
}
