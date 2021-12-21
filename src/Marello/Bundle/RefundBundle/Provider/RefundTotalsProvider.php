<?php

namespace Marello\Bundle\RefundBundle\Provider;

use Symfony\Contracts\Translation\TranslatorInterface;

use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;

use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator;
use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;

class RefundTotalsProvider implements FormChangesProviderInterface
{
    const SUBTOTAL = 'subtotal';
    const TAX_TOTAL = 'tax_total';
    const GRAND_TOTAL = 'grand_total';
    const NAME = 'marello.refund';
    const ITEMS_FIELD = 'items';
    const ADDITIONAL_ITEMS_FIELD = 'additionalItems';

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
     * RefundTotalsProvider constructor.
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
        $items = $submittedData[self::ITEMS_FIELD];
        if (isset($submittedData[self::ADDITIONAL_ITEMS_FIELD])) {
            $items = array_merge($submittedData[self::ITEMS_FIELD], $submittedData[self::ADDITIONAL_ITEMS_FIELD]);
        }
        $totals = $this->balanceCalculator->calculateTaxes($items, $refund);

        $currency = $refund->getCurrency();
        return [
            self::SUBTOTAL => [
                'amount' => $this->rounding->round($totals['subtotal']),
                'currency' => $currency,
                'visible' => true,
                'label' => $this->translator->trans(sprintf('%s.%s.label', self::NAME, 'subtotal'))
            ],
            self::TAX_TOTAL => [
                'amount' => $this->rounding->round($totals['taxTotal']),
                'currency' => $currency,
                'visible' => true,
                'label' => $this->translator->trans(sprintf('%s.%s.label', self::NAME, 'tax_total'))
            ],
            self::GRAND_TOTAL => [
                'amount' => $this->rounding->round($totals['grandTotal']),
                'currency' => $currency,
                'visible' => true,
                'label' => $this->translator->trans(sprintf('%s.%s.label', self::NAME, 'grand_total'))
            ]
        ];
    }
}
