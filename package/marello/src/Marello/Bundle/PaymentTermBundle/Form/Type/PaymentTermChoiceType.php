<?php

namespace Marello\Bundle\PaymentTermBundle\Form\Type;

use Marello\Bundle\PaymentTermBundle\Provider\PaymentTermProvider;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @inheritDoc
 */
class PaymentTermChoiceType extends AbstractType
{
    const BLOCK_PREFIX = 'payment_term_choice';

    protected $paymentTermProvider;
    protected $localizationHelper;

    public function __construct(PaymentTermProvider $paymentTermProvider, LocalizationHelper $localizationHelper)
    {
        $this->paymentTermProvider = $paymentTermProvider;
        $this->localizationHelper = $localizationHelper;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'choices' => $this->getChoices(),
            ])
        ;
    }

    /**
     * @return array
     */
    protected function getChoices()
    {
        $choices = [];
        foreach ($this->paymentTermProvider->getPaymentTerms() as $paymentTerm) {
            $label = $this->localizationHelper->getLocalizedValue($paymentTerm->getLabels())->getString();
            $choices[$label] = $paymentTerm->getId();
        }

        return $choices;
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
