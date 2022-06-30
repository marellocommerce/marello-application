<?php

namespace Marello\Bundle\PaymentBundle\Twig;

use Marello\Bundle\PaymentBundle\Event\PaymentMethodConfigDataEvent;
use Marello\Bundle\PaymentBundle\Checker\PaymentMethodEnabledByIdentifierCheckerInterface;
use Marello\Bundle\PaymentBundle\Formatter\PaymentMethodLabelFormatter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PaymentMethodExtension extends AbstractExtension
{
    const PAYMENT_METHOD_EXTENSION_NAME = 'marello_payment_method';
    const DEFAULT_METHOD_CONFIG_TEMPLATE
        = '@MarelloPayment/PaymentMethodsConfigsRule/paymentMethodWithOptions.html.twig';

    /**
     * @var PaymentMethodLabelFormatter
     */
    protected $paymentMethodLabelFormatter;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var PaymentMethodEnabledByIdentifierCheckerInterface
     */
    protected $checker;

    /**
     * @var array
     */
    protected $configCache = [];

    /**
     * @param PaymentMethodLabelFormatter                      $paymentMethodLabelFormatter
     * @param EventDispatcherInterface                          $dispatcher
     * @param PaymentMethodEnabledByIdentifierCheckerInterface $checker
     */
    public function __construct(
        PaymentMethodLabelFormatter $paymentMethodLabelFormatter,
        EventDispatcherInterface $dispatcher,
        PaymentMethodEnabledByIdentifierCheckerInterface $checker
    ) {
        $this->paymentMethodLabelFormatter = $paymentMethodLabelFormatter;
        $this->dispatcher = $dispatcher;
        $this->checker = $checker;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return static::PAYMENT_METHOD_EXTENSION_NAME;
    }

    /**
     * @param string $paymentMethodName
     *
     * @return string Payment Method config template path
     */
    public function getPaymentMethodConfigRenderData($paymentMethodName)
    {
        $event = new PaymentMethodConfigDataEvent($paymentMethodName);
        if (!array_key_exists($paymentMethodName, $this->configCache)) {
            $this->dispatcher->dispatch($event, PaymentMethodConfigDataEvent::NAME);
            $template = $event->getTemplate();
            if (!$template) {
                $template = static::DEFAULT_METHOD_CONFIG_TEMPLATE;
            }
            $this->configCache[$paymentMethodName] = $template;
        }

        return $this->configCache[$paymentMethodName];
    }

    /**
     * @param string $methodIdentifier
     *
     * @return bool
     */
    public function isPaymentMethodEnabled($methodIdentifier)
    {
        return $this->checker->isEnabled($methodIdentifier);
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'marello_get_payment_method_label',
                [$this->paymentMethodLabelFormatter, 'formatPaymentMethodLabel']
            ),
            new TwigFunction(
                'marello_payment_method_config_template',
                [$this, 'getPaymentMethodConfigRenderData']
            ),
            new TwigFunction(
                'marello_payment_method_enabled',
                [$this, 'isPaymentMethodEnabled']
            )
        ];
    }
}
