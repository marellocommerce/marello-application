<?php

namespace Marello\Bundle\CustomerBundle\Form\Type;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\AddressBundle\Form\Type\AddressType;
use Marello\Bundle\CustomerBundle\Entity\Company;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\PaymentTermBundle\Form\Type\PaymentTermSelectType;
use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CompanyType extends AbstractType
{
    const NAME = 'marello_customer_company';

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'marello.customer.company.name.label'])
            ->add('code', TextType::class, [
                'label' => 'marello.customer.company.code.label',
                'required' => false
            ])
            ->add(
                'parent',
                ParentCompanySelectType::class,
                [
                    'label' => 'marello.customer.company.parent.label',
                    'required' => false
                ]
            )
            ->add('paymentTerm', PaymentTermSelectType::class, [
                'label' => 'marello.customer.company.payment_term.label',
                'required' => false,
            ])
            ->add(
                'appendCustomers',
                EntityIdentifierType::class,
                [
                    'class'    => Customer::class,
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            )
            ->add(
                'removeCustomers',
                EntityIdentifierType::class,
                [
                    'class'    => Customer::class,
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            )
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit'])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'postSubmit']);

        if ($this->authorizationChecker->isGranted('marello_customer_company_address_update')) {
            $options = [
                'label' => 'marello.customer.company.addresses.label',
                'entry_type' => AddressType::class,
                'required' => true,
                'entry_options' => [
                    'data_class' => MarelloAddress::class,
                    'single_form' => false
                ]
            ];

            if (!$this->authorizationChecker->isGranted('marello_customer_company_address_create')) {
                $options['allow_add'] = false;
            }

            if (!$this->authorizationChecker->isGranted('marello_customer_company_address_remove')) {
                $options['allow_delete'] = false;
            }

            $builder
                ->add(
                    'addresses',
                    AddressCollectionType::class,
                    $options
                );
        }
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
    }

    /**
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
            'intention' => 'company',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
