<?php

namespace Marello\Bundle\PaymentBundle\Condition;

use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Oro\Component\Action\Condition\AbstractCondition;
use Oro\Component\ConfigExpression\ContextAccessorAwareInterface;
use Oro\Component\ConfigExpression\ContextAccessorAwareTrait;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * Check if payment method has payment rules
 * Usage:
 * @payment_method_has_payment_rules: method_identifier
 */
abstract class AbstractPaymentMethodHasPaymentRules extends AbstractCondition implements ContextAccessorAwareInterface
{
    use ContextAccessorAwareTrait;

    const NAME = 'marello_payment_method_has_payment_rules';

    /**
     * @var PropertyPathInterface
     */
    protected $propertyPath;

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(array $options)
    {
        $option = reset($options);
        $this->propertyPath = $option;

        if (!$this->propertyPath) {
            throw new \InvalidArgumentException('Missing "method_identifier" option');
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function isConditionAllowed($context)
    {
        $paymentMethodIdentifier = $this->resolveValue($context, $this->propertyPath, false);
        $methodConfigRules = $this->getRulesByMethod($paymentMethodIdentifier);

        return count($methodConfigRules) !== 0;
    }

    /**
     * @param $paymentMethodIdentifier
     *
     * @return PaymentMethodsConfigsRule[]
     */
    abstract protected function getRulesByMethod($paymentMethodIdentifier);

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return $this->convertToArray([$this->propertyPath]);
    }

    /**
     * {@inheritDoc}
     */
    public function compile($factoryAccessor)
    {
        return $this->convertToPhpCode([$this->propertyPath], $factoryAccessor);
    }
}
