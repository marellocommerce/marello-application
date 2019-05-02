<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Request\Factory;

use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContextInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Request\GoogleApiRequest;

class GeocodingApiRequestFactory implements GoogleApiRequestFactoryInterface
{
    const ADDRESS = 'address';
    const COMPONENTS = 'components';
    const COMPONENT_COUNTRY = 'country';
    const COMPONENT_ADMINISTRATIVE_AREA = 'administrative_area';
    const COMPONENT_POSTAL_CODE = 'postal_code';

    /**
     * {@inheritdoc}
     */
    public function createRequest(GoogleApiContextInterface $context)
    {
        $addressString = '';
        $params = [];
        if ($address = $context->getOriginAddress()) {
            if ($countryCode = $address->getCountryIso2()) {
                $params[self::COMPONENTS][self::COMPONENT_COUNTRY] = $countryCode;
            }
            if ($regionCode = $address->getRegionCode()) {
                $params[self::COMPONENTS][self::COMPONENT_ADMINISTRATIVE_AREA] = $regionCode;
            }
            if ($postalCode = $address->getPostalCode()) {
                $params[self::COMPONENTS][self::COMPONENT_POSTAL_CODE] = $postalCode;
            }
            if ($street = $address->getStreet()) {
                $addressString = $street;
            }
            if ($street2 = $address->getStreet2()) {
                $addressString = sprintf('%s %s', $addressString, $street2);
            }
            if ($city = $address->getCity()) {
                $addressString = sprintf('%s %s', $addressString, $city);
            }
            if (strlen($addressString) > 0) {
                $params[self::ADDRESS] = $addressString;
            }
        }
        
        return new GoogleApiRequest([
            GoogleApiRequest::FIELD_REQUEST_PARAMETERS => $params
        ]);
    }
}
