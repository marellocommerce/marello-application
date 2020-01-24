<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Marello\Bundle\ProductBundle\Entity\ProductChannelTaxRelation;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelSelectType;
use Marello\Bundle\TaxBundle\Form\Type\TaxCodeSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ProductChannelTaxRelationType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_product_channel_tax_relation_form';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('salesChannel', SalesChannelSelectType::class)
            ->add('taxCode', TaxCodeSelectType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => ProductChannelTaxRelation::class,
            'constraints'        => [new Valid()],
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
