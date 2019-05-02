<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Request\Factory;

use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContextInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Request\GoogleApiRequestInterface;

interface GoogleApiRequestFactoryInterface
{
    /**
     * @param GoogleApiContextInterface $context
     * @return GoogleApiRequestInterface
     */
    public function createRequest(GoogleApiContextInterface $context);
}
