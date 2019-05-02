<?php

namespace Marello\Bundle\ShippingBundle\Method\Provider\Label\Type;

use Marello\Bundle\ShippingBundle\Method\Exception\InvalidArgumentException;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;

class BasicMethodTypeLabelsProvider implements MethodTypeLabelsProviderInterface
{
    /**
     * @var ShippingMethodProviderInterface
     */
    private $methodProvider;

    /**
     * @param ShippingMethodProviderInterface $methodProvider
     */
    public function __construct(ShippingMethodProviderInterface $methodProvider)
    {
        $this->methodProvider = $methodProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabels($methodIdentifier, array $typeIdentifiers)
    {
        $method = $this->methodProvider->getShippingMethod($methodIdentifier);
        if (!$method) {
            throw new InvalidArgumentException(
                sprintf('Shipping method with identifier: %s, does not exist.', $methodIdentifier)
            );
        }

        $labels = [];
        foreach ($typeIdentifiers as $typeIdentifier) {
            $type = $method->getType($typeIdentifier);
            if (!$type) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Shipping method with identifier: %s does not contain type with identifier: %s.',
                        $methodIdentifier,
                        $typeIdentifier
                    )
                );
            }
            $labels[] = $type->getLabel();
        }

        return $labels;
    }
}
