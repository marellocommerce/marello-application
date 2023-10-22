<?php

namespace Marello\Bundle\PaymentBundle\Form\Type;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice;
use Marello\Bundle\InvoiceBundle\Entity\Repository\AbstractInvoiceRepository;
use Marello\Bundle\InvoiceBundle\Form\Type\InvoiceSelectType;
use Marello\Bundle\OrderBundle\Form\Type\OrderTotalPaidType;
use Marello\Bundle\PaymentBundle\Entity\Payment;
use Marello\Bundle\PaymentBundle\Migrations\Data\ORM\LoadPaymentStatusData;
use Marello\Bundle\PaymentBundle\Provider\PaymentMethodChoicesProviderInterface;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Valid;

class PaymentCreateType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_payment_create';

    public function __construct(
        protected ManagerRegistry $registry,
        protected PaymentMethodChoicesProviderInterface $paymentMethodChoicesProvider
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'paymentSource',
                InvoiceSelectType::class,
                [
                    'label'    => 'marello.payment.payment_source.label',
                    'required' => false,
                    'placeholder' => 'Choose Related Entity',
                    'empty_data'  => null,
                    'query_builder' => function (AbstractInvoiceRepository $repository) {
                        return $repository->createQueryBuilder('invoice')
                            ->where('invoice.totalPaid < invoice.grandTotal');
                    },
                ]
            )
            ->add(
                'paymentMethod',
                PaymentMethodSelectType::class,
                [
                    'label'    => 'marello.payment.payment_method.label',
                    'required' => true,
                    'constraints' => new NotNull,
                ]
            )
            ->add(
                'paymentDate',
                OroDateTimeType::class,
                [
                    'label'    => 'marello.payment.payment_date.label',
                    'required' => true,
                ]
            )
            ->add(
                'paymentReference',
                TextType::class,
                [
                    'label'    => 'marello.payment.payment_reference.label',
                    'required' => false
                ]
            )
            ->add(
                'paymentDetails',
                TextType::class,
                [
                    'label'    => 'marello.payment.payment_details.label',
                    'required' => false
                ]
            )
            ->add(
                'totalPaid',
                OrderTotalPaidType::class,
                [
                    'label'    => 'marello.payment.total_paid.label',
                    'required' => true,
                    'mapped' => false
                ]
            )
            ->add(
                'currency',
                HiddenType::class
            );
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $paymentSource = $form->get('paymentSource')->getData();
            if ($paymentSource instanceof AbstractInvoice) {
                $sourcePaymentMethod = $paymentSource->getPaymentMethod();
                if ($sourcePaymentMethod) {
                    $allPaymentMethods = $this->paymentMethodChoicesProvider->getMethods();
                    $choices = [$allPaymentMethods[$sourcePaymentMethod] => $sourcePaymentMethod];
                    $form->remove('paymentMethod');
                    $form->add(
                        'paymentMethod',
                        PaymentMethodSelectType::class,
                        [
                            'label' => 'marello.payment.payment_method.label',
                            'required' => true,
                            'choices' => $choices,
                            'constraints' => new NotNull,
                        ]
                    );
                }
                if ($paymentSource->getCurrency()) {
                    $form->remove('totalPaid');
                    $form->add(
                        'totalPaid',
                        OrderTotalPaidType::class,
                        [
                            'label'    => 'marello.payment.total_paid.label',
                            'required' => true,
                            'mapped' => false,
                            'currency' => $paymentSource->getCurrency()
                        ]
                    );
                }
            }
        });
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $payment = $event->getData();
            if ($payment instanceof Payment) {
                $form = $event->getForm();
                $totalPaid = $form->get('totalPaid')->getData();
                if ($totalPaid instanceof Price) {
                    $payment
                        ->setTotalPaid($totalPaid->getValue())
                        ->setCurrency($totalPaid->getCurrency());
                }
                $paymentSource = $form->get('paymentSource')->getData();
                if (!$paymentSource) {
                    $payment->setStatus($this->getStatus(LoadPaymentStatusData::UNASSIGNED));
                } else {
                    $payment->setStatus($this->getStatus(LoadPaymentStatusData::ASSIGNED));
                }
            }
            $event->setData($payment);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'  => Payment::class,
            'constraints' => [new Valid()]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

    /**
     * @param string $name
     * @return null|object
     */
    private function getStatus($name)
    {
        $statusClass = ExtendHelper::buildEnumValueClassName(LoadPaymentStatusData::PAYMENT_STATUS_ENUM_CLASS);
        $status = $this->registry
            ->getManagerForClass($statusClass)
            ->getRepository($statusClass)
            ->find($name);

        if ($status) {
            return $status;
        }

        return null;
    }
}
