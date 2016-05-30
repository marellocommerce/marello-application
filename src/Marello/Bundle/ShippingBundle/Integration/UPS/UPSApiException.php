<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS;

class UPSApiException extends \Exception
{
    /** @var array */
    protected $result;

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param array $result
     *
     * @return $this
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }
}
