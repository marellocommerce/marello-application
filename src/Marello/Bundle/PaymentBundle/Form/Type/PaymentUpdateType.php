<?php

namespace Marello\Bundle\PaymentBundle\Form\Type;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice;
use Marello\Bundle\InvoiceBundle\Form\Type\InvoiceSelectType;
use Marello\Bundle\PaymentBundle\Entity\Payment;
use Marello\Bundle\PaymentBundle\Migrations\Data\ORM\LoadPaymentStatusData;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Form\Type\PurchaseOrderItemCollectionType;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumSelectType;
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

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
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
                    'mapped'   => false
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
                    $oldSource->removePayment($payment);
                    $em->persist($oldSource);
                    $em->flush();
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
}
