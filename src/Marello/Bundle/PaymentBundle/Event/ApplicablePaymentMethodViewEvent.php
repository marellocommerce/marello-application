<?php

namespace Marello\Bundle\PaymentBundle\Event;

use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ApplicablePaymentMethodViewEvent extends Event
{
    const NAME = 'marello_payment.applicable_payment_method_view';

    /**
     * @var string
     */
    private $methodId;

    /**
     * @var string
     */
    private $methodLabel;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var PaymentContextInterface
     */
    private $paymentContext;

    /**
     * @param PaymentContextInterface $paymentContext
     * @param string $methodId
     * @param string $methodLabel
     * @param array $options
     */
    public function __construct(PaymentContextInterface $paymentContext, $methodId, $methodLabel, $options = [])
    {
        $this->paymentContext = $paymentContext;
        $this->methodId = $methodId;
        $this->methodLabel = $methodLabel;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getMethodId()
    {
        return $this->methodId;
    }

    /**
     * @param string $methodId
     * @return ApplicablePaymentMethodViewEvent
     */
    public function setMethodId($methodId)
    {
        $this->methodId = $methodId;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getMethodLabel()
    {
        return $this->methodLabel;
    }

    /**
     * @param string $methodLabel
     * @return ApplicablePaymentMethodViewEvent
     */
    public function setMethodLabel($methodLabel)
    {
        $this->methodLabel = $methodLabel;
        
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return ApplicablePaymentMethodViewEvent
     */
    public function setOptions($options)
    {
        $this->options = $options;
        
        return $this;
    }

    /**
     * @return PaymentContextInterface
     */
    public function getPaymentContext()
    {
        return $this->paymentContext;
    }

    /**
     * @param PaymentContextInterface $paymentContext
     * @return ApplicablePaymentMethodViewEvent
     */
    public function setPaymentContext($paymentContext)
    {
        $this->paymentContext = $paymentContext;
        
        return $this;
    }
}
