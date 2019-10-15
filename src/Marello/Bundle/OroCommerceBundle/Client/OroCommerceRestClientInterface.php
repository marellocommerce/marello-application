<?php

namespace Marello\Bundle\OroCommerceBundle\Client;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;

interface OroCommerceRestClientInterface extends RestClientInterface
{
    /**
     * Send PATCH request
     *
     * @param string $resource Resource name or url
     * @param mixed $data Request body
     * @param mixed $headers
     * @param mixed $options
     * @return RestResponseInterface
     * @throws RestException
     */
    public function patch($resource, $data, array $headers = array(), array $options = array());
}
