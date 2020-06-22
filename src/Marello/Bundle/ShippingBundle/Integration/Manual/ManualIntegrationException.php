<?php

namespace Marello\Bundle\ShippingBundle\Integration\Manual;

class ManualIntegrationException extends \Exception
{
    /** @var string */
    protected $rawResponse;

    /**
     * @return array
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * @param array $rawResponse
     *
     * @return $this
     */
    public function setRawResponse($rawResponse)
    {
        $this->rawResponse = $rawResponse;

        return $this;
    }
}
