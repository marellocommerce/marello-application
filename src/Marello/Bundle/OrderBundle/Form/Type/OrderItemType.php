<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Form\DataTransformer\TaxCodeToCodeTransformer;
use Marello\Bundle\OrderBundle\Form\EventListener\OrderItemPurchasePriceSubscriber;
use Marello\Bundle\ProductBundle\Form\Type\ProductSalesChannelAwareSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_order_item';

    /**
     * @var TaxCodeToCodeTransformer
     */
    protected $taxCodeModelTransformer;

    /**
     * @param TaxCodeToCodeTransformer $taxCodeModelTransformer
     */
    public function __construct(TaxCodeToCodeTransformer $taxCodeModelTransformer)
    {
        $this->taxCodeModelTransformer = $taxCodeModelTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', ProductSalesChannelAwareSelectType::class, [
                'required'       => true,
                'label'          => 'marello.product.entity_label',
                'create_enabled' => false,
            ])
            ->add('quantity', NumberType::class, [
                'data' => 1,
            ])->add('availableInventory', NumberType::class, [
                'mapped' => false,
                'attr' => [
                    'readonly' => true,
                ]
            ])
            ->add('price', TextType::class, [
                'attr' => [
                        'readonly' => true,
                    ]
            ])
            ->add('tax', TextType::class, [
                'attr' => [
                    'readonly' => true,
                ]
            ])
            ->add('taxCode', TextType::class, [
                'attr' => [
                    'readonly' => true,
                ]
            ])
            ->add('rowTotalExclTax', TextType::class, [
                'attr' => [
                    'readonly' => true,
                ]
            ])
            ->add('rowTotalInclTax', TextType::class, [
                'attr' => [
                    'readonly' => true,
                ]
            ])
        ;

        $builder->get('taxCode')->addModelTransformer($this->taxCodeModelTransformer);
        $builder->addEventSubscriber(new OrderItemPurchasePriceSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderItem::class
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
