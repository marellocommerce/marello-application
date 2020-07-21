<?php

namespace Marello\Bundle\PaymentBundle\Method\Provider;

use Marello\Bundle\PaymentBundle\Method\RemotePaymentMethod;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class CompositePaymentMethodProvider implements PaymentMethodProviderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var PaymentMethodProviderInterface[]
     */
    private $providers = [];

    /**
     * @param PaymentMethodProviderInterface $provider
     */
    public function addProvider(PaymentMethodProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentMethods()
    {
        $result = [];
        foreach ($this->providers as $provider) {
            $result = array_merge($result, $provider->getPaymentMethods());
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentMethod($identifier)
    {
        foreach ($this->providers as $provider) {
            if ($provider->hasPaymentMethod($identifier)) {
                return $provider->getPaymentMethod($identifier);
            }
        }

        $this->logger->warning(
            'There is no payment method found for given identifier.',
            [
                'identifier' => $identifier
            ]
        );

        return new RemotePaymentMethod($identifier);
    }

    /**
     * {@inheritDoc}
     */
    public function hasPaymentMethod($identifier)
    {
        foreach ($this->providers as $provider) {
            if ($provider->hasPaymentMethod($identifier)) {
                return true;
            }
        }

        return false;
    }
}
