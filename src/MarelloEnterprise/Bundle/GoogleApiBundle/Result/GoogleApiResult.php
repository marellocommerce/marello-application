<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Result;

use Symfony\Component\HttpFoundation\ParameterBag;

class GoogleApiResult extends ParameterBag implements GoogleApiResultInterface
{
    const FIELD_STATUS = 'status';
    const FIELD_ERROR_TYPE = 'error_type';
    const FIELD_ERROR_CODE = 'error_code';
    const FIELD_ERROR_MESSAGE = 'error_message';
    const FIELD_RESULT = 'result';
    
    const WARNING_TYPE = 'warning';
    const ERROR_TYPE = 'error';

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {
        return (bool)$this->get(self::FIELD_STATUS);
    }

    /**
     * {@inheritDoc}
     */
    public function getErrorType()
    {
        return $this->get(self::FIELD_ERROR_TYPE);
    }

    /**
     * {@inheritDoc}
     */
    public function getErrorCode()
    {
        return $this->get(self::FIELD_ERROR_CODE);
    }

    /**
     * {@inheritDoc}
     */
    public function getErrorMessage()
    {
        return $this->get(self::FIELD_ERROR_MESSAGE);
    }

    /**
     * {@inheritDoc}
     */
    public function getResult()
    {
        return $this->get(self::FIELD_RESULT);
    }
}
