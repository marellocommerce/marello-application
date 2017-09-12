<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Event;

use Symfony\Component\EventDispatcher\Event;

abstract class AbstractAddressGeocodingEvent extends Event
{
    const SUCCESS_TYPE = 'success';
    const WARNING_TYPE = 'warning';
    const ERROR_TYPE = 'error';
    
    /**
     * @var string
     */
    private $resultType;

    /**
     * @var string
     */
    private $resultMessage;

    /**
     * @return string
     */
    public function getResultType()
    {
        return $this->resultType;
    }

    /**
     * @param string $resultType
     * @return $this
     */
    public function setResultType($resultType)
    {
        $this->resultType = $resultType;

        return $this;
    }

    /**
     * @return string
     */
    public function getResultMessage()
    {
        return $this->resultMessage;
    }

    /**
     * @param string $resultMessage
     * @return $this
     */
    public function setResultMessage($resultMessage)
    {
        $this->resultMessage = $resultMessage;
        
        return $this;
    }
}
