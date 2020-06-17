<?php

namespace Marello\Bundle\PaymentBundle\Provider\FormChanges;

use Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice;
use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;
use Oro\Bundle\CurrencyBundle\Provider\CurrencyListProviderInterface;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class AvailableCurrenciesFormChangesProvider implements FormChangesProviderInterface
{
    const FIELD = 'currencies';

    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * @var CurrencyListProviderInterface
     */
    private $currencyListProvider;

    /**
     * @param LocaleSettings $localeSettings
     * @param CurrencyListProviderInterface $currencyListProvider
     */
    public function __construct(LocaleSettings $localeSettings, CurrencyListProviderInterface $currencyListProvider)
    {
        $this->localeSettings = $localeSettings;
        $this->currencyListProvider = $currencyListProvider;
    }

    /**
     * @inheritDoc
     */
    public function processFormChanges(FormChangeContextInterface $context)
    {
        $form = $context->getForm();
        $invoice = $form->get('paymentSource')->getData();
        $result = $context->getResult();
        $currencyChoices = [];
        if ($invoice instanceof AbstractInvoice) {
            $currencyChoices[$this->localeSettings->getCurrencySymbolByCurrency($invoice->getCurrency())] =
                $invoice->getCurrency();
        } else {
            foreach ($this->currencyListProvider->getCurrencyList() as $currency) {
                $currencyChoices[$this->localeSettings->getCurrencySymbolByCurrency($currency)] =
                    $currency;
            }
        }
        $result[self::FIELD] = $currencyChoices;
        $context->setResult($result);
    }
}