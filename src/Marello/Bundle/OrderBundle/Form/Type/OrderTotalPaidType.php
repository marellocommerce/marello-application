<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class OrderTotalPaidType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_order_total_paid';

    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * @param LocaleSettings $localeSettings
     */
    public function __construct(LocaleSettings $localeSettings)
    {
        $this->localeSettings = $localeSettings;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'value',
                NumberType::class,
                [
                    'grouping' => true,
                    'required' => true,
                    'constraints' => new NotNull
                ]
            )
            ->add(
                'currency',
                ChoiceType::class,
                [
                    'choices' => [
                        $this->localeSettings->getCurrencySymbolByCurrency($options['currency']) => $options['currency']
                    ]
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Price::class,
            'currency' => $this->localeSettings->getCurrency()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
