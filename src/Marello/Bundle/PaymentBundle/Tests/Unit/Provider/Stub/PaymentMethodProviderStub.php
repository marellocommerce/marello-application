<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Provider\Stub;

use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;

class PaymentMethodProviderStub implements PaymentMethodProviderInterface
{
    const METHOD_IDENTIFIER = 'test';

    /** @var PaymentMethodStub */
    protected $method;

    public function __construct()
    {
        $method = new PaymentMethodStub();
        $method->setIdentifier(self::METHOD_IDENTIFIER);

        $this->method = $method;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethods()
    {
        return [$this->method->getIdentifier() => $this->method];
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethod($name)
    {
        if ($name === $this->method->getIdentifier()) {
            return $this->method;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPaymentMethod($name)
    {
        return $name === $this->method->getIdentifier();
    }
}
