<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\AddressBundle\Form\Type\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotNull;

class CustomerApiType extends AbstractType
{
    const NAME = 'marello_customer_api';

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
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
            ->add('address', AddressType::NAME, [
                'required' => false,
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
