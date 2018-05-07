<?php

namespace Marello\Bundle\OroCommerceBundle\Request\Factory;

use Marello\Bundle\OroCommerceBundle\Request\OroCommerceRequest;
use Oro\Bundle\ApiBundle\Filter\FilterValue;
use Symfony\Component\HttpFoundation\ParameterBag;

interface OroCommerceRequestFactoryInterface
{
    const API_PATH = 'api';

    /**
     * @param $method
     * @param ParameterBag $settingsBag
     * @param string $resource
     * @param FilterValue[] $filters
     * @param array $include
     * @param array $data
     * @return OroCommerceRequest
     */
    public static function createRequest(
        $method,
        ParameterBag $settingsBag,
        $resource,
        array $filters = [],
        array $include = [],
        array $data = []
    );
}
