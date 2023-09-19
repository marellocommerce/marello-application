<?php

namespace Marello\Bundle\POSUserBundle\Api\Processor\Authenticate;

use Oro\Bundle\ApiBundle\Processor\SingleItemContext;

class AuthenticationContext extends SingleItemContext
{
    /** @var array */
    protected $requestData;

    public function getRequestData(): array
    {
        return $this->requestData;
    }

    public function setRequestData(array $requestData): void
    {
        $this->requestData = $requestData;
    }
}
