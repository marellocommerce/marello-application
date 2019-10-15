<?php

namespace Marello\Bundle\OroCommerceBundle\Generator;

use Symfony\Component\HttpFoundation\ParameterBag;

interface CacheKeyGeneratorInterface
{
    /**
     * @param ParameterBag $parameters
     * @return string
     */
    public function generateKey(ParameterBag $parameters);
}
