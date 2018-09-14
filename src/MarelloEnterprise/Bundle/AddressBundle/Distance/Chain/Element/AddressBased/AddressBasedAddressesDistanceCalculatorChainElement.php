<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Distance\Chain\Element\AddressBased;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Distance\Chain\Element\AbstractAddressesDistanceCalculatorChainElement;

class AddressBasedAddressesDistanceCalculatorChainElement extends AbstractAddressesDistanceCalculatorChainElement
{
    const COUNTRY_RATE = 10000;
    const REGION_RATE = 1000;
    const CITY_RATE = 100;
    const POSTAL_CODE_RATE = 10;

    /**
     * @inheritDoc
     */
    protected function getDistance(
        MarelloAddress $originAddress,
        MarelloAddress $destinationAddress,
        $unit = 'metric'
    ) {
        $distance = 0;
        if ($originAddress->getCountry() !== $destinationAddress->getCountry()) {
            return self::COUNTRY_RATE;
        }
        if ($originAddress->getRegion() !== $destinationAddress->getRegion()) {
            return self::REGION_RATE;
        }
        if ($originAddress->getCity() !== $destinationAddress->getCity()) {
            return self::CITY_RATE;
        }
        if ($originAddress->getPostalCode() !== $destinationAddress->getPostalCode()) {
            return self::POSTAL_CODE_RATE;
        }
        $streetMatchedParts = array_intersect(
            explode(
                ' ',
                sprintf('%s %s', $originAddress->getStreet(), $originAddress->getStreet2())
            ),
            explode(
                ' ',
                sprintf('%s %s', $destinationAddress->getStreet(), $destinationAddress->getStreet2())
            )
        );
        if (!empty($streetMatchedParts)) {
            return $distance - count($streetMatchedParts);
        }

        return $distance;
    }
}
