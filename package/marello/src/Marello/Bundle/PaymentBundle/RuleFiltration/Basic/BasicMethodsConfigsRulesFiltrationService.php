<?php

namespace Marello\Bundle\PaymentBundle\RuleFiltration\Basic;

use Marello\Bundle\PaymentBundle\Context\Converter\PaymentContextToRulesValueConverterInterface;
use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Marello\Bundle\PaymentBundle\RuleFiltration\MethodsConfigsRulesFiltrationServiceInterface;
use Marello\Bundle\RuleBundle\RuleFiltration\RuleFiltrationServiceInterface;

class BasicMethodsConfigsRulesFiltrationService implements MethodsConfigsRulesFiltrationServiceInterface
{
    /**
     * @var RuleFiltrationServiceInterface
     */
    private $filtrationService;

    /**
     * @var PaymentContextToRulesValueConverterInterface
     */
    private $paymentContextToRulesValueConverter;

    /**
     * @param RuleFiltrationServiceInterface       $filtrationService
     * @param PaymentContextToRulesValueConverterInterface $converter
     */
    public function __construct(
        RuleFiltrationServiceInterface $filtrationService,
        PaymentContextToRulesValueConverterInterface $converter
    ) {
        $this->filtrationService = $filtrationService;
        $this->paymentContextToRulesValueConverter = $converter;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilteredPaymentMethodsConfigsRules(
        array $paymentMethodsConfigsRules,
        PaymentContextInterface $context
    ) {
        $arrayContext = $this->paymentContextToRulesValueConverter->convert($context);

        return $this->filtrationService->getFilteredRuleOwners(
            $paymentMethodsConfigsRules,
            $arrayContext
        );
    }
}
