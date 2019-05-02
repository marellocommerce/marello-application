<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Marello\Bundle\PricingBundle\Form\Type\ProductPriceType;
use Marello\Bundle\ProductBundle\Form\Type\ProductSupplierSelectType;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Marello\Bundle\PurchaseOrderBundle\Validator\Constraints\PurchaseOrderItemConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseOrderItemType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_purchase_order_item';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', ProductSupplierSelectType::class, [
                'label'          => 'marello.product.entity_label',
                'create_enabled' => false,
            ])
            ->add('orderedAmount', NumberType::class, [
                'label' => 'Ordered Amount'
            ])
            ->add('purchasePrice', ProductPriceType::class, [
                'label' => 'Purchase Price',
                'currency' => $options['currency'],
                'currency_symbol' => $options['currency_symbol']
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'currency' => null,
            'currency_symbol' => null,
            'data_class' => PurchaseOrderItem::class,
            'constraints' => [
                new PurchaseOrderItemConstraint()
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
