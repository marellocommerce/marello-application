<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;

class PurchaseOrderItemType extends AbstractType
{
    const NAME = 'marello_purchase_order_item';

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
