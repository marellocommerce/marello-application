<?php

namespace Marello\Bundle\PaymentTermBundle\Form\Type;

use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentTermSelectType extends AbstractType
{
    const BLOCK_PREFIX = 'payment_term_select';

    protected $localizationHelper;

    public function __construct(LocalizationHelper $localizationHelper)
    {
        $this->localizationHelper = $localizationHelper;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $localizationHelper = $this->localizationHelper;

        $resolver->setDefaults([
            'class' => PaymentTerm::class,
            'choice_label' =>  function (PaymentTerm $paymentTerm) use ($localizationHelper) {
                return $localizationHelper->getLocalizedValue($paymentTerm->getLabels());
            },
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }

    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
