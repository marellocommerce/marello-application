<?php

namespace Marello\Bundle\CustomerBundle\Form\Type;

use Marello\Bundle\AddressBundle\Form\Type\AddressType;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class CustomerType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_customer';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('company', CompanySelectType::class, [
                'required' => false,
                'create_enabled' => false,
            ])
            ->add('namePrefix', TextType::class, [
                'required' => false,
            ])
            ->add('firstName', TextType::class, [
                'required'    => true
            ])
            ->add('middleName', TextType::class, [
                'required' => false,
            ])
            ->add('lastName', TextType::class, [
                'required'    => true
            ])
            ->add('nameSuffix', TextType::class, [
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'required'    => true
            ])
            ->add('primaryAddress', AddressType::class, [
                'required' => false,
            ])
            ->add('shippingAddress', AddressType::class, [
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
            'constraints'          => [new Valid()],
            'allow_extra_fields'   => true,
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
