<?php

namespace Marello\Bundle\PricingBundle\Form\Type;

use Marello\Bundle\PricingBundle\Entity\AssembledPriceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssembledPriceListType extends AbstractType
{
    const NAME = 'marello_assembled_price_list';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currency', HiddenType::class, [
                'required' => true,
            ])
            ->add('defaultPrice', ProductPriceType::class, [
                'required' => true,
            ])
            ->add('specialPrice', ProductPriceType::class, [
                'required' => false,
            ])
            ->add('msrpPrice', ProductPriceType::class, [
                'required' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => AssembledPriceList::class,
            'intention'         => 'assembledpricelist',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
