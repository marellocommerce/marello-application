<?php

namespace Marello\Bundle\AddressBundle\Form\Type;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraint;

class AddressType extends AbstractType
{
    const NAME = 'marello_address';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('label');
        $builder->remove('organization');

        $builder
            ->add('phone', 'text', [
                'required' => false,
            ])
            ->add('company', 'text', [
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
        return 'oro_address';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
