<?php

namespace Marello\Bundle\OrderBundle\Event;

use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Symfony\Contracts\EventDispatcher\Event;

class OrderPaymentContextBuildingEvent extends Event
{
    const NAME = 'marello_order.order_payment_context_building';

    /**
     * @var PaymentContextInterface
     */
    private $paymentContext;

    /**
     * @param PaymentContextInterface $paymentContext
     */
    public function __construct(PaymentContextInterface $paymentContext)
    {
        $this->paymentContext = $paymentContext;
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
     * @return $this
     */
    public function setPaymentContextBuilder(PaymentContextInterface $paymentContext)
    {
        $this->paymentContext = $paymentContext;
        
        return $this;
    }
}
