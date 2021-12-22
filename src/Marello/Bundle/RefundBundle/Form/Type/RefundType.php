<?php

namespace Marello\Bundle\RefundBundle\Form\Type;

use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Entity\RefundItem;
use Marello\Bundle\RefundBundle\Form\EventListener\CurrencySubscriber;
use Marello\Bundle\RefundBundle\Form\EventListener\RefundTotalsSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RefundType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_refund';
    const VALIDATION_MESSAGE = 'Refund must contain at least one refunded item, or additional custom refunded item.';

    /** @var RefundTotalsSubscriber $refundTotalsSubscriber */
    protected $refundTotalsSubscriber;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'items',
                OrderItemRefundCollectionType::class
            );

        $builder
            ->addEventListener(
                FormEvents::POST_SET_DATA,
                function (FormEvent $event) {
                    /** @var Refund $data */
                    $data = $event->getData();
                    $form = $event->getForm();

                    $orderedItems = $data->getItems()->filter(
                        function (RefundItem $item) {
                            return $item->getOrderItem() !== null;
                        }
                    );

                    $additionalItems = $data->getItems()->filter(
                        function (RefundItem $item) {
                            return $item->getOrderItem() === null;
                        }
                    );

                    $form->get('items')->setData($orderedItems);
                    $form
                        ->add(
                            'additionalItems',
                            AdditionalRefundCollectionType::class,
                            [
                                'mapped' => false,
                                'entry_options' => [
                                    'currency' => $data->getCurrency()
                                ]
                            ]
                        );
                    $form->get('additionalItems')->setData($additionalItems);
                }
            )
            ->addEventListener(
                FormEvents::SUBMIT,
                function (FormEvent $event) {
                    /** @var Refund $data */
                    $data = $event->getData();
                    $form = $event->getForm();

                    /** @var RefundItem[] $additionalItems */
                    $additionalItems = $form->get('additionalItems')->getData();

                    foreach ($additionalItems as $item) {
                        $data->addItem($item);
                    }

                    $event->setData($data);
                }
            )
            ->addEventSubscriber(new CurrencySubscriber())
            ->addEventSubscriber($this->refundTotalsSubscriber)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Refund::class,
                'constraints' => [
                    new Callback(function (Refund $refund, ExecutionContextInterface $context) {
                        if ($refund->getItems()->count() === 0) {
                            $context
                                ->buildViolation(self::VALIDATION_MESSAGE)
                                ->atPath('items')
                                ->addViolation()
                            ;
                        }
                    })
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

    /**
     * @param RefundTotalsSubscriber $refundTotalsSubscriber
     */
    public function setRefundTotalSubscriber(RefundTotalsSubscriber $refundTotalsSubscriber)
    {
        $this->refundTotalsSubscriber = $refundTotalsSubscriber;
    }
}
