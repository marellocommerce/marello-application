<?php

namespace Marello\Bundle\UPSBundle\Migrations\Data\ORM\Config;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Provider\ChannelType;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;

class UPSConfigToSettingsConverter
{
    /**
     * @var string
     */
    private $productionUrl;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var SymmetricCrypterInterface
     */
    private $crypter;

    /**
     * @param string $productionUrl
     * @param ManagerRegistry $doctrine
     * @param SymmetricCrypterInterface $crypter
     */
    public function __construct($productionUrl, ManagerRegistry $doctrine, SymmetricCrypterInterface $crypter)
    {
        $this->productionUrl = $productionUrl;
        $this->doctrine = $doctrine;
        $this->crypter = $crypter;
    }

    /**
     * @param UPSConfig $config
     *
     * @return mixed
     */
    public function convert(UPSConfig $config)
    {
        $settings = new UPSSettings();

        $settings
            ->addLabel((new LocalizedFallbackValue())->setString(strtoupper(ChannelType::TYPE)))
            ->setUpsApiUser($config->getUser())
            ->setUpsApiPassword($this->crypter->encryptData($config->getPassword()))
            ->setUpsApiKey($config->getAccessLicenseKey())
            ->setUpsShippingAccountName($config->getShippingAccountName())
            ->setUpsShippingAccountNumber($config->getShippingAccountNumber())
            ->setUpsPickupType($config->getPickupType())
            ->setUpsUnitOfWeight($config->getUnitOfWeight())
            ->addApplicableShippingService($this->getServiceByCode($config->getShippingServiceCode()))
            ->setUpsTestMode($this->isTestMode($config->getBaseUrl()));

        if ($config->getCountryCode()) {
            $settings->setUpsCountry($this->getCountryByCode($config->getCountryCode()));
        }

        return $settings;
    }

    /**
     * @param string $code
     * @return Country
     */
    private function getCountryByCode($code)
    {
        return $this->doctrine
            ->getManagerForClass(Country::class)
            ->getRepository(Country::class)
            ->find($code);
    }

    /**
     * @param string $code
     * @return ShippingService
     */
    private function getServiceByCode($code)
    {
        return $this->doctrine
            ->getManagerForClass(ShippingService::class)
            ->getRepository(ShippingService::class)
            ->findOneBy(['code' => $code]);
    }

    /**
     * @param string $baseUrl
     * @return bool
     */
    private function isTestMode($baseUrl)
    {
        if (strpos($baseUrl, $this->productionUrl) !== false) {
            return false;
        }

        return true;
    }
}
