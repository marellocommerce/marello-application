<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory;

use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContextInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResult;

class GeocodingApiResultFactory extends AbstractGoogleApiResultFactory
{
    const API_NAME = 'Geocoding';

    const LATITUDE = 'lat';
    const LONGITUDE = 'lng';
    const FORMATTED_ADDRESS = 'formatted_address';

    /**
     * {@inheritdoc}
     */
    protected function createSuccessResult(array $data)
    {
        return [
            GoogleApiResult::FIELD_STATUS => true,
            GoogleApiResult::FIELD_RESULT => [
                self::LATITUDE => $this->getValueByKeyRecursively($this->getLocation($data), self::LATITUDE),
                self::LONGITUDE => $this->getValueByKeyRecursively($this->getLocation($data), self::LONGITUDE),
                self::FORMATTED_ADDRESS => $this->getValueByKeyRecursively(
                    $this->getPrimaryResult($data),
                    self::FORMATTED_ADDRESS
                ),
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getZeroResultsErrorMessage(GoogleApiContextInterface $context)
    {
        return sprintf(
            "Google Maps Geocoding API can't find coordinates for %s",
            $context->getOriginAddress()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getResponseStatus(array $data)
    {
        return $data['status'];
    }
    
    /**
     * @param array $data
     *
     * @return array
     * @throws \LogicException
     */
    private function getPrimaryResult(array $data)
    {
        return $this->getValueByKeyRecursively($data, 'results')[0];
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws \LogicException
     */
    private function getGeometry(array $data)
    {
        return $this->getValueByKeyRecursively($this->getPrimaryResult($data), 'geometry');
    }

    /**
     * @param array $data
     *
     * @return string
     * @throws \LogicException
     */
    private function getLocation(array $data)
    {
        return $this->getValueByKeyRecursively($this->getGeometry($data), 'location');
    }
}
