<?php

namespace Marello\Bundle\UPSBundle\Cache;

use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Model\Request\PriceRequest;

class ShippingPriceCacheKey
{
    /**
     * @var UPSSettings
     */
    private $transport;

    /**
     * @var PriceRequest
     */
    private $priceRequest;

    /**
     * @var string
     */
    private $methodId;

    /**
     * @var string
     */
    private $typeId;

    /**
     * @return UPSSettings
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @param UPSSettings $transport
     * @return $this
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * @return PriceRequest
     */
    public function getPriceRequest()
    {
        return $this->priceRequest;
    }

    /**
     * @param PriceRequest $request
     * @return $this
     */
    public function setPriceRequest($request)
    {
        $this->priceRequest = $request;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethodId()
    {
        return $this->methodId;
    }

    /**
     * @param string $methodId
     * @return $this
     */
    public function setMethodId($methodId)
    {
        $this->methodId = $methodId;
        return $this;
    }

    /**
     * @return string
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * @param string $typeId
     * @return $this
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;
        return $this;
    }

    /**
     * @return string
     */
    public function generateKey()
    {
        $requestData = json_decode($this->priceRequest->stringify(), true);
        if (array_key_exists('Service', $requestData['RateRequest']['Shipment'])) {
            unset($requestData['RateRequest']['Shipment']['Service']);
        }
        unset($requestData['UPSSecurity'], $requestData['RateRequest']['Request']);

        return implode('_', [
            md5(serialize($requestData)),
            $this->methodId,
            $this->typeId,
            $this->transport ? $this->transport->getId() : null,
        ]);
    }
}
