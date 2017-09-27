<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory;

use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContextInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResultInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;

interface GoogleApiResultFactoryInterface
{
    /**
     * @param RestResponseInterface $response
     * @param GoogleApiContextInterface $context
     *
     * @return GoogleApiResultInterface
     */
    public function createResult(RestResponseInterface $response, GoogleApiContextInterface $context);

    /**
     * @param \Exception $exception
     *
     * @return GoogleApiResultInterface
     */
    public function createExceptionResult(\Exception $exception);
}
