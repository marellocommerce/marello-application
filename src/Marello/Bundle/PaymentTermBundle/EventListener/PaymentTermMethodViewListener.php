<?php

namespace Marello\Bundle\PaymentTermBundle\EventListener;

use Marello\Bundle\PaymentBundle\Event\ApplicablePaymentMethodViewEvent;
use Marello\Bundle\PaymentTermBundle\Provider\PaymentTermProvider;

class PaymentTermMethodViewListener
{
    /**
     * @var PaymentTermProvider
     */
    private $paymentTermProvider;

    /**
     * @param PaymentTermProvider $paymentTermProvider
     */
    public function __construct(PaymentTermProvider $paymentTermProvider)
    {
        $this->paymentTermProvider = $paymentTermProvider;
    }

    /**
     * @param ApplicablePaymentMethodViewEvent $event
     */
    public function onApplicablePaymentMethodView(ApplicablePaymentMethodViewEvent $event)
    {

        if (strpos($event->getMethodId(), 'payment_term') !== false) {
            $context = $event->getPaymentContext();
            $options = $event->getOptions();
            $paymentTerm = null;
            if ($context->getCustomer()) {
                $paymentTerm = $this->paymentTermProvider->getCustomerPaymentTerm($context->getCustomer());
            }
            if (!$paymentTerm) {
                $paymentTerm = $this->paymentTermProvider->getDefaultPaymentTerm();
            }
            if ($paymentTerm) {
                $options['code'] = $paymentTerm->getCode();
                $options['term'] = $paymentTerm->getTerm();
                $event->setOptions($options);
            }
        }
    }
}