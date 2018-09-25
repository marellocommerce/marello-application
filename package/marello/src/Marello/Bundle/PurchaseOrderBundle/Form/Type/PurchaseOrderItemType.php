<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Marello\Bundle\PricingBundle\Form\Type\ProductPriceType;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Marello\Bundle\PurchaseOrderBundle\Validator\Constraints\PurchaseOrderItemConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotNull;

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
    public function getName()
    {
        return self::NAME;
    }
}
