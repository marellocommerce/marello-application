<?php

namespace Marello\Bundle\TaxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ProductChannelTaxRelationType extends AbstractType
{
    const NAME = 'marello_product_channel_tax_relation_form';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('salesChannel', 'marello_sales_saleschannel_select')
//            ->add('taxCode', 'marello_tax_taxcode_select')
            ->add('taxCode', EntityType::class, array(
                'class' => 'MarelloTaxBundle:TaxCode',
                'choice_label' => 'code',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Marello\Bundle\TaxBundle\Entity\ProductChannelTaxRelation',
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
