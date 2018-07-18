<?php

namespace Marello\Bundle\UPSBundle\Migrations\Data\ORM\Config;

class UPSConfigKeysProviderFactory
{
    /**
     * @return UPSConfigKeysProvider
     */
    public static function create()
    {
        return new UPSConfigKeysProvider([
            UPSConfigKeysProvider::BASE_URL_KEY => 'ups_api_base_url',
            UPSConfigKeysProvider::USER_KEY => 'ups_username',
            UPSConfigKeysProvider::PASSWORD_KEY => 'ups_password',
            UPSConfigKeysProvider::LICENSE_KEY => 'ups_access_license_key',
            UPSConfigKeysProvider::SHIPPING_ACCOUNT_NUMBER_KEY => 'ups_account_number',
            UPSConfigKeysProvider::SHIPPING_ACCOUNT_NAME_KEY => 'shipper_name',
            UPSConfigKeysProvider::COUNTRY_KEY => 'shipper_address_country_code'
        ]);
    }
}
