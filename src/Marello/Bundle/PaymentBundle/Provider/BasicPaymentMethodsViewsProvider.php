<?php

namespace Marello\Bundle\PaymentBundle\Provider;

use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Marello\Bundle\PaymentBundle\Event\ApplicablePaymentMethodViewEvent;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodViewCollection;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodViewFactory;
use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\MethodsConfigsRulesByContextProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BasicPaymentMethodsViewsProvider implements PaymentMethodsViewsProviderInterface
{
    /**
     * @var MethodsConfigsRulesByContextProviderInterface
     */
    protected $paymentRulesProvider;

    /**
     * @var PaymentMethodProviderInterface
     */
    protected $paymentMethodProvider;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param MethodsConfigsRulesByContextProviderInterface $paymentRulesProvider
     * @param PaymentMethodProviderInterface                $paymentMethodProvider
     * @param EventDispatcherInterface                      $eventDispatcher
     */
    public function __construct(
        MethodsConfigsRulesByContextProviderInterface $paymentRulesProvider,
        PaymentMethodProviderInterface $paymentMethodProvider,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->paymentRulesProvider = $paymentRulesProvider;
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @inheritDoc
     */
    public function getApplicableMethodsViews(PaymentContextInterface $context)
    {
        $methodCollection = new PaymentMethodViewCollection();

        $rules = $this->paymentRulesProvider->getPaymentMethodsConfigsRules($context);
        foreach ($rules as $rule) {
            foreach ($rule->getMethodConfigs() as $methodConfig) {
                $methodId = $methodConfig->getMethod();
                $method = $this->paymentMethodProvider->getPaymentMethod($methodId);

                if (!$method) {
                    continue;
                }
                $event = new ApplicablePaymentMethodViewEvent(
                    $context,
                    $methodId,
                    $method->getLabel(),
                    $methodConfig->getOptions()
                );
                $this->eventDispatcher->dispatch($event, ApplicablePaymentMethodViewEvent::NAME);
                $methodView = PaymentMethodViewFactory::createMethodView(
                    $event->getMethodId(),
                    $event->getMethodLabel(),
                    $method->getSortOrder(),
                    $event->getOptions()
                );

                $methodCollection->addMethodView($methodId, $methodView);
            }
        }

        return $methodCollection;
    }
}
