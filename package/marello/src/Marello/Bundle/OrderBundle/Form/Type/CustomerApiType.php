<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\AddressBundle\Form\Type\AddressType;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotNull;

class CustomerApiType extends AbstractType
{
    const NAME = 'marello_customer_api';

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * CustomerApiType constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

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
            ->add('primaryAddress', AddressType::NAME, [
                'required'    => true,
                'constraints' => [
                    new NotNull,
                ],
            ])
            ->add('shippingAddress', AddressType::NAME, [
                'required'    => true,
                'constraints' => [
                    new NotNull,
                ],
            ])
        ;

        $builder
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                /** @var Customer */
                $data = $event->getData();

                if (!$data) {
                    return;
                }

                /** @var UsernamePasswordOrganizationToken $token */
                $token = $this->tokenStorage->getToken();

                $data->setOrganization($token->getOrganizationContext());
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class'      => Customer::class,
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
