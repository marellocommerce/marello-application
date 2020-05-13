<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Request;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class GoogleApiRequest extends ParameterBag implements GoogleApiRequestInterface
{
    const FIELD_REQUEST_PARAMETERS = 'requestParameters';

    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params);
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestParameters()
    {
        return $this->get(self::FIELD_REQUEST_PARAMETERS);
    }
}
