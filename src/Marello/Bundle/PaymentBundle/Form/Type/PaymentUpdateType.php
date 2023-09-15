<?php

namespace Marello\Bundle\PaymentBundle\Form\Type;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice;
use Marello\Bundle\InvoiceBundle\Entity\Repository\AbstractInvoiceRepository;
use Marello\Bundle\InvoiceBundle\Form\Type\InvoiceSelectType;
use Marello\Bundle\PaymentBundle\Entity\Payment;
use Marello\Bundle\PaymentBundle\Migrations\Data\ORM\LoadPaymentStatusData;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumSelectType;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class PaymentUpdateType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_payment_update';

    public function __construct(
        protected ManagerRegistry $registry
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'status',
                EnumSelectType::class,
                [
                    'label'     => 'marello.payment.status.label',
                    'enum_code' => 'marello_paymnt_status',
                    'configs'   => ['allowClear' => false]
                ]
            )
            ->add(
                'paymentSource',
                InvoiceSelectType::class,
                [
                    'label'    => 'marello.payment.payment_source.label',
                    'required' => true,
                    'placeholder' => 'Choose Related Entity',
                    'empty_data'  => null
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
            );
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var Payment $payment */
            $payment = $event->getData();
            if ($payment) {
                $form = $event->getForm();
                if ($payment->getStatus() && $payment->getStatus()->getId() == LoadPaymentStatusData::ASSIGNED) {
                    $form->remove('status');
                    $form->add(
                        'status',
                        EnumSelectType::class,
                        [
                            'label'     => 'marello.payment.status.label',
                            'disabled'  => true,
                            'enum_code' => 'marello_paymnt_status',
                            'configs'   => ['allowClear' => false]
                        ]
                    );
                    $form->remove('paymentSource');
                    $form->add(
                        'paymentSource',
                        InvoiceSelectType::class,
                        [
                            'label'    => 'marello.payment.payment_source.label',
                            'required' => true,
                            'mapped'   => false,
                            'disabled' => true,
                            'placeholder' => 'Choose Related Entity',
                            'empty_data'  => null,
                        ]
                    );
                } else {
                    $currency = $payment->getCurrency();
                    $form->remove('paymentSource');
                    $form->add(
                        'paymentSource',
                        InvoiceSelectType::class,
                        [
                            'label'    => 'marello.payment.payment_source.label',
                            'required' => true,
                            'mapped'   => false,
                            'placeholder' => 'Choose Related Entity',
                            'empty_data'  => null,
                            'query_builder' => function (AbstractInvoiceRepository $repository) use ($currency) {
                                return $repository->createQueryBuilder('invoice')
                                    ->where('invoice.currency = :currency')
                                    ->andWhere('invoice.totalPaid < invoice.grandTotal')
                                    ->setParameter('currency', $currency);
                            },
                        ]
                    );
                }
                $source = $this->registry
                    ->getManagerForClass(AbstractInvoice::class)
                    ->getRepository(AbstractInvoice::class)
                    ->findOneByPayment($payment);
                if ($source) {
                    $form->get('paymentSource')->setData($source);
                }
            }
        });
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var Payment $payment */
            $payment = $event->getData();
            $form = $event->getForm();
            $source = $form->get('paymentSource')->getData();
            if ($source instanceof AbstractInvoice) {
                $payment->setPaymentSource($source);
            }
            if ($source) {
                $payment->setStatus($this->getStatus(LoadPaymentStatusData::ASSIGNED));
            }

            $event->setData($payment);
        });
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Payment $payment */
            $payment = $event->getData();
            if ($payment) {
                $form = $event->getForm();
                $newSource = $form->get('paymentSource')->getData();
                $oldSource = $this->registry
                    ->getManagerForClass(AbstractInvoice::class)
                    ->getRepository(AbstractInvoice::class)
                    ->findOneByPayment($payment);
                if ($newSource !== $oldSource) {
                    $em = $this->registry->getManagerForClass(AbstractInvoice::class);
                    if ($oldSource) {
                        $oldSource->removePayment($payment);
                        $em->persist($oldSource);
                        $em->flush();
                    }
                    $newSource->addPayment($payment);
                    $em->persist($newSource);
                    $em->flush();
                }
            }
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
