<?php

namespace Marello\Bundle\SalesBundle\Form\Type;

use Marello\Bundle\SalesBundle\Entity\SalesChannelType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesChannelTypeType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_saleschannel_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'marello.sales.saleschanneltype.name.label',
                    'required' => true
                ]
            )
            ->add(
                'label',
                TextType::class,
                [
                    'label' => 'marello.sales.saleschanneltype.label.label',
                    'required' => true
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'  => SalesChannelType::class
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
