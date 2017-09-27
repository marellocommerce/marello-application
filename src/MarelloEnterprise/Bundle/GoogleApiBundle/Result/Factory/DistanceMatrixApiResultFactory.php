<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory;

use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContextInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResult;

class DistanceMatrixApiResultFactory extends AbstractGoogleApiResultFactory
{
    const API_NAME = 'Distance Matrix';

    const DISTANCE = 'distance';
    const DURATION = 'duration';

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    protected function getZeroResultsErrorMessage(GoogleApiContextInterface $context)
    {
        return sprintf(
            "Google Maps Distance Matrix API can't calculate distance between %s and %s",
            $context->getOriginAddress(),
            $context->getDestinationAddress()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getResponseStatus(array $data)
    {
        $primaryElement = $this->getPrimaryElement($data);
        return $primaryElement ? $primaryElement['status'] : $data['status'];
    }
    
    /**
     * @param array $data
     *
     * @return array
     * @throws \LogicException
     */
    private function getPrimaryRow(array $data)
    {
        $rows = $this->getValueByKeyRecursively($data, 'rows');
        if (count($rows) > 0) {
            return $rows[0];
        }
        
        return null;
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws \LogicException
     */
    private function getPrimaryElement(array $data)
    {
        $primaryRow = $this->getPrimaryRow($data);
        if ($primaryRow) {
            $elements = $this->getValueByKeyRecursively($primaryRow, 'elements');
            if (count($elements) > 0) {
                return $elements[0];
            }
            
            return null;
        }

        return null;
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
