<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Authenticate;

use Oro\Bundle\ApiBundle\Processor\SingleItemContext;

class AuthenticationContext extends SingleItemContext
{
    /** @var array */
    protected $requestData;

    /**
     * Returns request data.
     *
     * @return array
     */
    public function getRequestData()
    {
        return $this->requestData;
    }

    /**
     * Sets request data to the Context.
     *
     * @param array $requestData
     */
    public function setRequestData(array $requestData)
    {
        $this->requestData = $requestData;
    }
}
