<?php

namespace Marello\Bundle\RefundBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Marello\Bundle\RefundBundle\Entity\RefundItem;
use Marello\Bundle\RefundBundle\Form\DataTransformer\TaxCodeToIdTransformer;

class OrderItemRefundType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_order_item_refund';

    /**
     * @var TaxCodeToIdTransformer
     */
    protected $taxCodeModelTransformer;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'quantity',
                NumberType::class,
                [
                    'empty_data' => 0
                ]
            )
            ->add('taxCode', TextType::class, [
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('refundAmount', MoneyType::class);
        $builder->get('taxCode')->addModelTransformer($this->taxCodeModelTransformer);
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var RefundItem $item */
                $item = $event->getData();
                $form = $event->getForm();

                if ($item === null) {
                    $form->add('refundAmount', MoneyType::class);
                    return;
                }

                if ($item->getOrderItem()) {
                    $item->setTaxCode($item->getOrderItem()->getTaxCode());
                }
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
     * @param TaxCodeToIdTransformer $taxCodeToIdTransformer
     */
    public function setTaxCodeTransformer(TaxCodeToIdTransformer $taxCodeToIdTransformer)
    {
        $this->taxCodeModelTransformer = $taxCodeToIdTransformer;
    }
}
