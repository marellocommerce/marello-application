<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\PaymentBundle\Context\Builder\Factory\PaymentContextBuilderFactoryInterface;
use Marello\Bundle\PaymentBundle\Context\PaymentContextFactoryInterface;
use Marello\Bundle\PaymentBundle\Provider\PaymentMethodsViewsProviderInterface;

class PossiblePaymentMethodsProvider implements FormChangesProviderInterface
{
    const POSSIBLE_PAYMENT_METHODS_KEY = 'possiblePaymentMethods';
    
    /**
     * @var PaymentContextBuilderFactoryInterface
     */
    protected $factory;

    /**
     * @var PaymentMethodsViewsProviderInterface
     */
    protected $paymentMethodsViewsProvider;

    /**
     * @param PaymentContextFactoryInterface $factory
     * @param PaymentMethodsViewsProviderInterface $paymentMethodsViewsProvider
     */
    public function __construct(
        PaymentContextFactoryInterface $factory,
        PaymentMethodsViewsProviderInterface $paymentMethodsViewsProvider
    ) {
        $this->factory = $factory;
        $this->paymentMethodsViewsProvider = $paymentMethodsViewsProvider;
    }
    
    /**
     * {@inheritdoc}
     */
    public function processFormChanges(FormChangeContextInterface $context)
    {
        $form = $context->getForm();
        $order = $form->getData();
        $result = $context->getResult();
        $result[self::POSSIBLE_PAYMENT_METHODS_KEY] = $this->getPossiblePaymentMethods($order);
        $context->setResult($result);
    }

    /**
     * @param Order $order
     * @return array
     */
    private function getPossiblePaymentMethods(Order $order)
    {
        $data = [];
        $paymentContextArray = $this->factory->create($order);
        $paymentContext = !empty($paymentContextArray) ? reset($paymentContextArray) : null;
        if (!$paymentContext) {
            return $data;
        }
        $data = $this->paymentMethodsViewsProvider
            ->getApplicableMethodsViews($paymentContext)
            ->toArray();
        
        return $data;
    }
}
