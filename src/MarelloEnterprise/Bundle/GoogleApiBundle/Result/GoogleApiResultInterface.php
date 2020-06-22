<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Result;

interface GoogleApiResultInterface
{
    /**
     * @return bool
     */
    public function getStatus();


    /**
     * @return string
     */
    public function getErrorType();

    /**
     * @return string
     */
    public function getErrorCode();

    /**
     * @return string|null
     */
    public function getErrorMessage();
    
    /**
     * @return array|null
     */
    public function getResult();
}
