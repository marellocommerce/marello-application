<?php

namespace Marello\Bundle\POSUserBundle\Api\Processor;

use Marello\Bundle\POSUserBundle\Api\Processor\Authenticate\AuthenticationContext;
use Oro\Bundle\ApiBundle\Processor\NormalizeResultContext;
use Oro\Bundle\ApiBundle\Processor\RequestActionProcessor;

class AuthenticateRequestActionProcessor extends RequestActionProcessor
{
    protected function createContextObject()
    {
        return new AuthenticationContext($this->configProvider, $this->metadataProvider);
    }

    protected function getLogContext(NormalizeResultContext $context): array
    {
        // remove id from result
        $result = parent::getLogContext($context);
        if (array_key_exists('id', $result) && empty($result['id'])) {
            unset($result['id']);
        }

        return $result;
    }
}
