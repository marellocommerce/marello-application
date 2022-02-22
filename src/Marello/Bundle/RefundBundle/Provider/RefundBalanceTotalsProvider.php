<?php

namespace Marello\Bundle\RefundBundle\Provider;

use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;
use Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RefundBalanceTotalsProvider implements FormChangesProviderInterface
{
    const BALANCE = 'balance';
    const TOTAL = 'total';
    const REFUNDS_TOTAL = 'refundstotal';
    const NAME = 'marello.refund';

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
     * @param RefundBalanceCalculator $balanceCalculator
     * @param RoundingServiceInterface $rounding
     * @param TranslatorInterface $translator
     */
    public function __construct(
        RefundBalanceCalculator $balanceCalculator,
        RoundingServiceInterface $rounding,
        TranslatorInterface $translator
    ) {
        $this->balanceCalculator = $balanceCalculator;
        $this->rounding = $rounding;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function processFormChanges(FormChangeContextInterface $context)
    {
        $form = $context->getForm();
        $order = $form->getData();
        $result = $context->getResult();
        $result['balance_totals'] = $this->getTotalWithSubtotalsValues($order);
        $context->setResult($result);
    }

    /**
     * Calculate and return total with subtotals
     * and with values in base currency converted to Array
     * Used by Orders
     *
     * @param Refund $refund
     * @return array
     */
    protected function getTotalWithSubtotalsValues(Refund $refund)
    {
        $balance = $this->balanceCalculator->caclulateBalance($refund);
        $refundsTotal = $this->balanceCalculator->caclulateRefundsTotal($refund);
        $currency = $refund->getCurrency();
        return [
            self::BALANCE => [
                'amount' => $this->rounding->round($balance),
                'currency' => $currency,
                'visible' => true,
                'label' => $this->translator->trans(sprintf('%s.%s.label', self::NAME, 'refund_balance'))
            ],
            self::REFUNDS_TOTAL => [
                'amount' => $this->rounding->round($refundsTotal),
                'currency' => $currency,
                'visible' => true,
                'label' => $this->translator->trans(sprintf('%s.%s.label', self::NAME, 'refunds_total'))
            ]
        ];
    }
}
