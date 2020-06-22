<?php

namespace Marello\Bundle\UPSBundle\Migrations\Data\ORM\Config;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class UPSConfigKeysProvider extends ParameterBag
{
    const BASE_URL_KEY = 'ups_api_base_url';
    const USER_KEY = 'ups_username';
    const PASSWORD_KEY = 'ups_password';
    const LICENSE_KEY = 'ups_access_license_key';
    const SHIPPING_ACCOUNT_NUMBER_KEY = 'ups_account_number';
    const SHIPPING_ACCOUNT_NAME_KEY = 'shipper_name';
    const COUNTRY_KEY = 'shipper_address_country_code';

    /**
     * {@inheritDoc}
     */
    public function __construct(array $parameters = [])
    {
        parent::__construct($parameters);
    }

    /**
     * @return string
     */
    public function getBaseUrlKey()
    {
        return $this->get(self::BASE_URL_KEY);
    }
    
    /**
     * @return string
     */
    public function getUserKey()
    {
        return $this->get(self::USER_KEY);
    }

    /**
     * @return string
     */
    public function getPasswordKey()
    {
        return $this->get(self::PASSWORD_KEY);
    }

    /**
     * @return string
     */
    public function getLisenceKey()
    {
        return $this->get(self::LICENSE_KEY);
    }

    /**
     * @return string
     */
    public function getShippingAccountNumberKey()
    {
        return $this->get(self::SHIPPING_ACCOUNT_NUMBER_KEY);
    }

    /**
     * @return string
     */
    public function getShippingAccountNameKey()
    {
        return $this->get(self::SHIPPING_ACCOUNT_NAME_KEY);
    }

    /**
     * @return string
     */
    public function getCountryKey()
    {
        return $this->get(self::COUNTRY_KEY);
    }
}
