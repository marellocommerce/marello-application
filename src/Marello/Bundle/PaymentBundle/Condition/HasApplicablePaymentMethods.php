<?php

namespace Marello\Bundle\PaymentBundle\Condition;

use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Marello\Bundle\PaymentBundle\Provider\PaymentMethodsViewsProviderInterface;
use Oro\Component\ConfigExpression\Condition\AbstractCondition;
use Oro\Component\ConfigExpression\ContextAccessorAwareInterface;
use Oro\Component\ConfigExpression\ContextAccessorAwareTrait;
use Oro\Component\ConfigExpression\Exception\InvalidArgumentException;

/**
 * Check applicable payment methods
 * Usage:
 * @marello_has_applicable_payment_methods:
 *      entity: ~
 */
class HasApplicablePaymentMethods extends AbstractCondition implements ContextAccessorAwareInterface
{
    use ContextAccessorAwareTrait;

    const NAME = 'marello_has_applicable_payment_methods';

    /** @var PaymentMethodProviderInterface */
    protected $paymentMethodProvider;

    /** @var PaymentMethodsViewsProviderInterface */
    protected $paymentMethodsViewsProvider;

    /** @var mixed */
    protected $paymentContext;

    /**
     * @param PaymentMethodProviderInterface $paymentMethodProvider
     * @param PaymentMethodsViewsProviderInterface $paymentMethodsViewsProvider
     */
    public function __construct(
        PaymentMethodProviderInterface $paymentMethodProvider,
        PaymentMethodsViewsProviderInterface $paymentMethodsViewsProvider
    ) {
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->paymentMethodsViewsProvider = $paymentMethodsViewsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (array_key_exists('paymentContext', $options)) {
            $this->paymentContext = $options['paymentContext'];
        } elseif (array_key_exists(0, $options)) {
            $this->paymentContext = $options[0];
        }

        if (!$this->paymentContext) {
            throw new InvalidArgumentException('Missing "paymentContext" option');
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    protected function isConditionAllowed($context)
    {
        /** @var PaymentContextInterface $paymentContext */
        $paymentContext = $this->resolveValue($context, $this->paymentContext, false);

        $methodsData = [];
        if (null !== $paymentContext) {
            $methodsData = $this->paymentMethodsViewsProvider->getApplicableMethodsViews($paymentContext);
        }

        return count($methodsData) !== 0;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->convertToArray([$this->paymentContext]);
    }

    /**
     * {@inheritdoc}
     */
    public function compile($factoryAccessor)
    {
        return $this->convertToPhpCode([$this->paymentContext], $factoryAccessor);
    }
}
