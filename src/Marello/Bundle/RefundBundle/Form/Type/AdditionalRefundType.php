<?php

namespace Marello\Bundle\RefundBundle\Form\Type;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

use Marello\Bundle\RefundBundle\Entity\RefundItem;
use Marello\Bundle\TaxBundle\Form\Type\TaxCodeSelectType;

class AdditionalRefundType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_additional_refund';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotNull(),
                ]
            ])
            ->add(
                'taxCode',
                TaxCodeSelectType::class
            );

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                /** @var RefundItem $item */
                $item = $event->getData();
                $form = $event->getForm();

                if ($item === null) {
                    $form->add('refundAmount', MoneyType::class, [
                        'empty_data' => 0,
                        'currency' => $options['currency'],
                        'constraints' => [
                            new GreaterThan(0),
                        ]
                    ]);

                    return;
                }

                $form
                    ->add('refundAmount', MoneyType::class, [
                        'empty_data' => 0,
                        'currency' => $item->getRefund()->getCurrency(),
                        'constraints' => [
                            new GreaterThan(0),
                        ]
                    ]);
            });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => RefundItem::class,
                'currency'   => null
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
}
