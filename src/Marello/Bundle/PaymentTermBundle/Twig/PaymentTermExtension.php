<?php

namespace Marello\Bundle\PaymentTermBundle\Twig;

use Marello\Bundle\PaymentTermBundle\Provider\PaymentTermProvider;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PaymentTermExtension extends AbstractExtension
{
    const NAME = 'marello_payment_term';

    /**
     * @var PaymentTermProvider
     */
    protected $paymentTermProvider;

    /**
     * @var LocalizationHelper
     */
    protected $localizationHelper;

    /**
     * @param PaymentTermProvider $paymentTermProvider
     * @param LocalizationHelper $localizationHelper
     */
    public function __construct(PaymentTermProvider $paymentTermProvider, LocalizationHelper $localizationHelper)
    {
        $this->paymentTermProvider = $paymentTermProvider;
        $this->localizationHelper = $localizationHelper;
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'marello_get_payment_term_label_for_code',
                [$this, 'getPaymentTermLabelForCode']
            ),
            new TwigFunction(
                'marello_get_payment_term_for_customer',
                [$this->paymentTermProvider, 'getCustomerPaymentTerm']
            )
        ];
    }

    /**
     * @param string $code
     * @return string
     */
    public function getPaymentTermLabelForCode($code)
    {
        $paymentTerm = $this->paymentTermProvider->getPaymentTerm($code);
        if ($paymentTerm) {
            return $this->localizationHelper->getLocalizedValue($paymentTerm->getLabels())->getString();
        }
    }
}
