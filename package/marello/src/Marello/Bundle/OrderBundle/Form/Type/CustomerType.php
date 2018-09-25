<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\AddressBundle\Form\Type\AddressType;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotNull;

class CustomerType extends AbstractType
{
    const NAME = 'marello_customer';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('namePrefix', 'text', [
                'required' => false,
            ])
            ->add('firstName', 'text', [
                'required'    => true,
                'constraints' => new NotNull,
            ])
            ->add('middleName', 'text', [
                'required' => false,
            ])
            ->add('lastName', 'text', [
                'required'    => true,
                'constraints' => new NotNull,
            ])
            ->add('nameSuffix', 'text', [
                'required' => false,
            ])
            ->add('email', 'email', [
                'required'    => true,
                'constraints' => [
                    new NotNull,
                    new Email,
                ],
            ])
            ->add('taxIdentificationNumber', 'text', [
                'required' => false,
            ])
            ->add('primaryAddress', AddressType::NAME, [
                'required' => false,
            ])
            ->add('shippingAddress', AddressType::NAME, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'           => Customer::class,
            'intention'            => 'customer',
            'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
            'cascade_validation'   => true,
            'allow_extra_fields'   => true,
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return self::NAME;
    }
}
