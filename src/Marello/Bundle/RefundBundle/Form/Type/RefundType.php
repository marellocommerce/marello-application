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
    const NAME = 'marello_refund';
    const VALIDATION_MESSAGE = 'Refund must contain at least one refunded item, or additional custom refunded item.';

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'items',
                OrderItemRefundCollectionType::NAME
            )
            ->add(
                'additionalItems',
                AdditionalRefundCollectionType::NAME,
                [
                    'mapped' => false,
                ]
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

                    $filtered = $data->getItems()->filter(function (RefundItem $item) {
                        return $item->getRefundAmount();
                    });

                    $data->setItems($filtered);

                    $event->setData($data);
                }
            )
            ->addEventSubscriber(new CurrencySubscriber())
            ->addEventSubscriber(new RefundTotalsSubscriber())
        ;
    }

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
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return self::NAME;
    }
}
