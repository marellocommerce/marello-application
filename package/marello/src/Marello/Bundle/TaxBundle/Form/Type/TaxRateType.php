<?php

namespace Marello\Bundle\TaxBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class TaxRateType extends AbstractType
{
    const NAME = 'marello_tax_rate_form';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'code',
                TextType::class,
                [
                    'required' => true,
                    'constraints' => new NotNull()
                ]
            )
            ->add(
                'rate',
                OroPercentType::class,
                [
                    'label' => 'marello.tax.taxrate.rate.label',
                    'required' => true,
                    'constraints' => new NotNull()
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Marello\Bundle\TaxBundle\Entity\TaxRate',
            'cascade_validation' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
