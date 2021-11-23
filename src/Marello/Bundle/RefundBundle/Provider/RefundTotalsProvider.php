<?php

namespace Marello\Bundle\RefundBundle\Provider;

use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;
use Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;

class RefundTotalsProvider implements FormChangesProviderInterface
{
    const BALANCE = 'balance';
    const TOTAL = 'total';
    const AMOUNT = 'amount';

    /**
     * @var RefundBalanceCalculator
     */
    protected $balanceCalculator;

    /**
     * @var \NumberFormatter
     */
    protected $numberFormatter;

    /**
     * @param RefundBalanceCalculator $balanceCalculator
     */
    public function __construct(RefundBalanceCalculator $balanceCalculator, NumberFormatter $numberFormatter)
    {
        $this->balanceCalculator = $balanceCalculator;
        $this->numberFormatter = $numberFormatter;
    }

    /**
     * @param float $value
     * @param string $currencyCode
     *
     * @return string
     */
    public function formatValue($value, $currencyCode)
    {
        return $this->numberFormatter->formatCurrency($value, $currencyCode);
    }

    /**
     * {@inheritdoc}
     */
    public function processFormChanges(FormChangeContextInterface $context)
    {
        $form = $context->getForm();
        $order = $form->getData();
        $result = $context->getResult();
        $result['totals'] = $this->getTotalWithSubtotalsValues($order);
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
        $amount = $this->balanceCalculator->caclulateAmount($refund);
        $currency = $refund->getCurrency();

        return [
            self::BALANCE => $this->formatValue($balance, $currency),
            self::TOTAL => $this->formatValue($balance + $amount, $currency),
            self::AMOUNT => $this->formatValue($amount, $currency)
        ];
    }
}
