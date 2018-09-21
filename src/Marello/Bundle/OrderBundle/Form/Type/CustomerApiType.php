<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\AddressBundle\Form\Type\AddressType;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotNull;

class CustomerApiType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_customer_api';

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
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
            ->add('namePrefix', TextType::class, [
                'required' => false,
            ])
            ->add('firstName', TextType::class, [
                'required'    => true,
                'constraints' => new NotNull,
            ])
            ->add('middleName', TextType::class, [
                'required' => false,
            ])
            ->add('lastName', TextType::class, [
                'required'    => true,
                'constraints' => new NotNull,
            ])
            ->add('nameSuffix', TextType::class, [
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'required'    => true,
                'constraints' => [
                    new NotNull,
                    new Email,
                ],
            ])
            ->add('primaryAddress', AddressType::class, [
                'required'    => true,
                'constraints' => [
                    new NotNull,
                ],
            ])
            ->add('shippingAddress', AddressType::class, [
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
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
