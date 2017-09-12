<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory;

use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResult;

class DistanceMatrixApiResultFactory extends AbstractGoogleApiResultFactory
{
    const API_NAME = 'Distance Matrix';

    const DISTANCE = 'distance';
    const DURATION = 'duration';

    /**
     * {@inheritDoc}
     */
    public function createSuccessResult(array $data)
    {
        return [
            GoogleApiResult::FIELD_STATUS => true,
            GoogleApiResult::FIELD_RESULT => [
                self::DISTANCE => $this->getValueByKeyRecursively($this->getDistance($data), 'value'),
                self::DURATION => $this->getValueByKeyRecursively($this->getDuration($data), 'value'),
            ]
        ];
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws \LogicException
     */
    private function getPrimaryRow(array $data)
    {
        return $this->getValueByKeyRecursively($data, 'rows')[0];
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws \LogicException
     */
    private function getPrimaryElement(array $data)
    {
        return $this->getValueByKeyRecursively($this->getPrimaryRow($data), 'elements')[0];
    }

    /**
     * @param array $data
     *
     * @return string
     * @throws \LogicException
     */
    private function getDistance(array $data)
    {
        return $this->getValueByKeyRecursively($this->getPrimaryElement($data), self::DISTANCE);
    }

    /**
     * @param array $data
     *
     * @return string
     * @throws \LogicException
     */
    private function getDuration(array $data)
    {
        return $this->getValueByKeyRecursively($this->getPrimaryElement($data), self::DURATION);
    }
}
