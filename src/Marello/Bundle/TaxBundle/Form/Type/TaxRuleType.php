<?php

namespace Marello\Bundle\TaxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaxRuleType extends AbstractType
{
    const NAME = 'marello_tax_rule_form';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('taxCode', TaxCodeSelectType::class, [
                'label' => 'marello.tax.taxrule.tax_code.label',
                'required' => true
            ])
            ->add('taxRate', TaxRateSelectType::class, [
                'label' => 'marello.tax.taxrule.tax_rate.label',
                'required' => true
            ])
            ->add('taxJurisdiction', TaxJurisdictionSelectType::class, [
                'label' => 'marello.tax.taxrule.tax_jurisdiction.label',
                'required' => true
            ])
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
