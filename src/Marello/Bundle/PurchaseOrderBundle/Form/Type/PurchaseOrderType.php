<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Validator\Constraints\PurchaseOrderConstraint;
use Marello\Bundle\SupplierBundle\Form\Type\SupplierSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PurchaseOrderType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_purchase_order';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'supplier',
                SupplierSelectType::class,
                [
                    'attr'           => ['readonly' => true],
                    'required'       => true,
                    'label'          => 'marello.supplier.entity_label',
                    'create_enabled' => false,
                ]
            )
            ->add(
                'items',
                PurchaseOrderItemCollectionType::class
            )
            ->add(
                'dueDate',
                OroDateType::class,
                [
                    'required' => false,
                    'label' => 'marello.purchaseorder.expected_delivery_date.label',
                ]
            )
            ->add(
                'purchaseOrderReference',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'marello.purchaseorder.purchase_order_reference.label'
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PurchaseOrder::class,
            'constraints' => [
                new PurchaseOrderConstraint()
            ],
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
