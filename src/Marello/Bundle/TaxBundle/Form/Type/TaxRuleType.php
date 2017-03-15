<?php

namespace Marello\Bundle\TaxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class TaxRuleType extends AbstractType
{
    const NAME = 'marello_taxrule_form';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('taxCode', EntityType::class, array(
                'class' => 'MarelloTaxBundle:TaxCode',
                'choice_label' => 'code',
            ))
            ->add('taxRate', EntityType::class, array(
                'class' => 'MarelloTaxBundle:TaxRate'
            ))
            ->add('includesVat')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Marello\Bundle\TaxBundle\Entity\TaxRule',
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
