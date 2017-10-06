<?php

namespace Marello\Bundle\TaxBundle\Provider;

use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Provider\OrderItem\AbstractOrderItemFormChangesProvider;
use Marello\Bundle\TaxBundle\Calculator\TaxCalculatorInterface;
use Marello\Bundle\TaxBundle\Matcher\TaxRuleMatcherInterface;

class OrderItemRowTotalsProvider extends AbstractOrderItemFormChangesProvider
{
    /**
     * @var TaxCalculatorInterface
     */
    protected $taxCalculator;

    /**
     * @var TaxRuleMatcherInterface
     */
    protected $taxRuleMatcher;

    /**
     * @param TaxCalculatorInterface $taxCalculator
     * @param TaxRuleMatcherInterface $taxRuleMatcher
     */
    public function __construct(
        TaxCalculatorInterface $taxCalculator,
        TaxRuleMatcherInterface $taxRuleMatcher
    ) {
        $this->taxCalculator = $taxCalculator;
        $this->taxRuleMatcher = $taxRuleMatcher;
    }

    public function processFormChanges(FormChangeContextInterface $context)
    {
        /** @var Order $order */
        $order = $context->getForm()->getData();
        $submittedData = $context->getSubmittedData();
        $result = $context->getResult();
        if (!isset($result[self::ITEMS_FIELD]) || !isset($result[self::ITEMS_FIELD]['price'])  ||
        !isset($result[self::ITEMS_FIELD]['tax_code'])) {
            $result[self::ITEMS_FIELD] = [];
            $result[self::ITEMS_FIELD]['row_totals'] = [];
            $context->setResult($result);
            return null;
        }
        $itemResult = $result[self::ITEMS_FIELD];
        foreach ($submittedData[self::ITEMS_FIELD] as $item) {
            if (!empty($item['product'])) {
                $identifier = sprintf('%s%s', self::IDENTIFIER_PREFIX, $item['product']);
                if (isset($itemResult['price'][$identifier]) && isset($itemResult['tax_code'][$identifier]) &&
                    isset($item['quantity'])
                ) {
                    $taxRule = $this->taxRuleMatcher->match(
                        $order->getShippingAddress(),
                        [$itemResult['tax_code'][$identifier]['code']]
                    );
                    if ($taxRule) {
                        $rate = $taxRule->getTaxRate()->getRate();
                    } else {
                        $rate = 0;
                    }
                    $amount = (double)$itemResult['price'][$identifier]['value'] * (float)$item['quantity'];
                    $taxTotals = $this->taxCalculator->calculate($amount, $rate);
                    $itemResult['row_totals'][$identifier] = $taxTotals->jsonSerialize();
                }
            }
        }
        $result[self::ITEMS_FIELD] = $itemResult;
        $context->setResult($result);
    }
}
