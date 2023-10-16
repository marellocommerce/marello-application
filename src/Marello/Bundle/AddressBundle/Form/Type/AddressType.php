<?php

namespace Marello\Bundle\AddressBundle\Form\Type;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Oro\Bundle\AddressBundle\Form\Type\AddressType as OroAddressType;

class AddressType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_address';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('label');
        $builder->remove('organization');

        $builder
            ->add('phone', TextType::class, [
                'required' => false,
            ])
            ->add('company', TextType::class, [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MarelloAddress::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroAddressType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
