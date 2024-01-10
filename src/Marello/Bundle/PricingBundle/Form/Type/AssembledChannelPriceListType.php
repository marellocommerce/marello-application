<?php

namespace Marello\Bundle\PricingBundle\Form\Type;

use Marello\Bundle\PricingBundle\Entity\AssembledChannelPriceList;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssembledChannelPriceListType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_assembled_channel_price_list';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('channel', SalesChannelSelectType::class, [
                'excluded'    => $options['excluded_channels']
            ])
            ->add('currency', HiddenType::class, [
                'required' => true,
            ])
            ->add('defaultPrice', ProductChannelPriceType::class, [
                'required' => true,
                'allowed_empty_value' => false
            ])
            ->add('specialPrice', ProductChannelPriceType::class, [
                'required' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => AssembledChannelPriceList::class,
            'intention'         => 'assembledchannelpricelist',
            'single_form'       => true,
            'excluded_channels' => []
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
