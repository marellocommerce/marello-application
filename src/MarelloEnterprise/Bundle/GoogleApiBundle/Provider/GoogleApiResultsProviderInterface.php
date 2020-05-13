<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Provider;

use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContextInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResultInterface;

interface GoogleApiResultsProviderInterface
{
    /**
     * @param GoogleApiContextInterface $context
     * @return GoogleApiResultInterface|null
     */
    public function getApiResults(GoogleApiContextInterface $context);
}
