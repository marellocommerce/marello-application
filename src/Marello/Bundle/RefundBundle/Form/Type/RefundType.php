<?php

namespace Marello\Bundle\RefundBundle\Form\Type;

use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Entity\RefundItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RefundType extends AbstractType
{
    const NAME = 'marello_refund';

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
                FormEvents::POST_SUBMIT,
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
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Refund::class,
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
