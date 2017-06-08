<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PurchaseOrderItemType extends AbstractType
{
    const NAME = 'marello_purchase_order_item';

    const VALIDATION_MESSAGE_PRODUCT = 'Product can not be null';
    const VALIDATION_MESSAGE_ORDERED_AMOUNT = 'Ordered Amount must be higher than 0';


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', 'marello_product_supplier_select', [
                'label'          => 'marello.product.entity_label',
                'create_enabled' => false,
            ])
            ->add('orderedAmount', 'number', [
                'label' => 'Ordered Amount',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PurchaseOrderItem::class,
            'constraints' => [
                new Callback(function (PurchaseOrderItem $purchaseOrderItem, ExecutionContextInterface $context) {
                    if ($purchaseOrderItem->getProduct() == null) {
                        $context
                            ->buildViolation(self::VALIDATION_MESSAGE_PRODUCT)
                            ->atPath('product')
                            ->addViolation()
                        ;
                    }

                    if ($purchaseOrderItem->getOrderedAmount() == null || $purchaseOrderItem->getOrderedAmount() <= 0 ) {
                        $context
                            ->buildViolation(self::VALIDATION_MESSAGE_ORDERED_AMOUNT)
                            ->atPath('orderedAmount')
                            ->addViolation()
                        ;
                    }
                })
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

}
