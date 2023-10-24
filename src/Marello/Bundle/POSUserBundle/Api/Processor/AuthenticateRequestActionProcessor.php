<?php

namespace Marello\Bundle\POSUserBundle\Api\Processor;

use Oro\Component\ChainProcessor\Context;
use Oro\Bundle\ApiBundle\Processor\NormalizeResultContext;
use Oro\Bundle\ApiBundle\Processor\RequestActionProcessor;

use Marello\Bundle\POSUserBundle\Api\Processor\Authenticate\AuthenticationContext;

class AuthenticateRequestActionProcessor extends RequestActionProcessor
{
    protected function createContextObject(): Context
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
