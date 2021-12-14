<?php

namespace Marello\Bundle\RefundBundle\Provider;

use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;
use Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\TaxBundle\Calculator\TaxCalculatorInterface;
use Marello\Bundle\TaxBundle\Matcher\TaxRuleMatcherInterface;
use Marello\Bundle\TaxBundle\Model\ResultElement;
use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RefundTotalsProvider implements FormChangesProviderInterface
{
    const SUBTOTAL = 'subtotal';
    const TAX_TOTAL = 'tax_total';
    const GRAND_TOTAL = 'grand_total';
    const NAME = 'marello.refund';
    const ITEMS_FIELD = 'items';

    /**
     * @var RefundBalanceCalculator
     */
    protected $balanceCalculator;

    /**
     * @var RoundingServiceInterface
     */
    protected $rounding;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var TaxCalculatorInterface
     */
    protected $taxCalculator;

    /**
     * @var TaxRuleMatcherInterface
     */
    protected $taxRuleMatcher;

    /**
     * RefundTotalsProvider constructor.
     * @param RefundBalanceCalculator $balanceCalculator
     * @param RoundingServiceInterface $rounding
     * @param TranslatorInterface $translator
     * @param TaxCalculatorInterface $taxCalculator
     * @param TaxRuleMatcherInterface $taxRuleMatcher
     */
    public function __construct(
        RefundBalanceCalculator $balanceCalculator,
        RoundingServiceInterface $rounding,
        TranslatorInterface $translator,
        TaxCalculatorInterface $taxCalculator,
        TaxRuleMatcherInterface $taxRuleMatcher
    ) {
        $this->balanceCalculator = $balanceCalculator;
        $this->rounding = $rounding;
        $this->translator = $translator;
        $this->taxCalculator = $taxCalculator;
        $this->taxRuleMatcher = $taxRuleMatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function processFormChanges(FormChangeContextInterface $context)
    {
        $form = $context->getForm();
        $refund = $form->getData();
        $result = $context->getResult();
        $result['totals'] = $this->getTotalWithSubtotalsValues($refund, $context->getSubmittedData());
        $context->setResult($result);
    }

    /**
     * @param Refund $refund
     * @param array $result
     * @return array[]
     * @throws \Oro\Bundle\CurrencyBundle\Exception\InvalidRoundingTypeException
     */
    protected function getTotalWithSubtotalsValues(Refund $refund, array $submittedData)
    {
        $subtotal = 0;
        $taxTotal = 0;
        $grandTotal = 0;
        foreach ($submittedData[self::ITEMS_FIELD] as $rowIdentifierKey => $item) {
            $taxRule = $this->taxRuleMatcher->match(
                $refund->getOrder()->getShippingAddress(),
                [$item['taxCode']]
            );
            if ($taxRule) {
                $rate = $taxRule->getTaxRate()->getRate();
            } else {
                $rate = 0;
            }
            $amount = (double)$item['refundAmount'] * (float)$item['quantity'];
            /** @var ResultElement $taxTotals */
            $taxTotals = $this->taxCalculator->calculate($amount, $rate);
            $subtotal += (double)$taxTotals->getExcludingTax();
            $taxTotal += (double)$taxTotals->getTaxAmount();
            $grandTotal += (double)$taxTotals->getIncludingTax();
        }

        $currency = $refund->getCurrency();
        return [
            self::SUBTOTAL => [
                'amount' => $this->rounding->round($subtotal),
                'currency' => $currency,
                'visible' => true,
                'label' => $this->translator->trans(sprintf('%s.%s.label', self::NAME, 'subtotal'))
            ],
            self::TAX_TOTAL => [
                'amount' => $this->rounding->round($taxTotal),
                'currency' => $currency,
                'visible' => true,
                'label' => $this->translator->trans(sprintf('%s.%s.label', self::NAME, 'tax_total'))
            ],
            self::GRAND_TOTAL => [
                'amount' => $this->rounding->round($grandTotal),
                'currency' => $currency,
                'visible' => true,
                'label' => $this->translator->trans(sprintf('%s.%s.label', self::NAME, 'grand_total'))
            ]
        ];
    }
}
