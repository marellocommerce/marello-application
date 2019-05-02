<?php

namespace Marello\Bundle\ShippingBundle\Method\Provider\Label\Type;

use Marello\Bundle\ShippingBundle\Method\Exception\InvalidArgumentException;

interface MethodTypeLabelsProviderInterface
{
    /**
     * @param string   $methodIdentifier
     * @param string[] $typeIdentifiers
     *
     * @return string[]
     *
     * @throws InvalidArgumentException
     */
    public function getLabels($methodIdentifier, array $typeIdentifiers);
}
