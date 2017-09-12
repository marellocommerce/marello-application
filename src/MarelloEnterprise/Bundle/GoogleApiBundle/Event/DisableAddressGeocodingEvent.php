<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Event;

class DisableAddressGeocodingEvent extends AbstractAddressGeocodingEvent
{
    const NAME = 'marelloenterprise_google_api.address_geocoding.disable';
}
