<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Form\Type\InventoryItemApiType;
use Marello\Bundle\InventoryBundle\Form\DataTransformer\InventoryItemUpdateApiTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class ProductApiType extends AbstractType
{
    const NAME = 'marello_product_api_form';

    /** @var InventoryItemUpdateApiTransformer $transformer */
    protected $transformer;

    public function __construct(InventoryItemUpdateApiTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('sku')
            ->add('status', 'entity', [
                'class' => 'Marello\Bundle\ProductBundle\Entity\ProductStatus',
            ])
            ->add(
                'weight',
                'number',
                [
                    'required' => false,
                    'scale' => 2,
                ]
            )
            ->add(
                'desiredStockLevel',
                'number',
                [
                    'constraints' => new NotNull(),
                ]
            )
            ->add(
                'purchaseStockLevel',
                'number',
                [
                    'constraints' => new NotNull(),
                ]
            )
            ->add('prices')
            ->add('channels')
            ->add('inventory', 'collection', [
                'property_path' => 'inventoryItems',
                'type'          => new InventoryItemApiType($this->transformer),
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'Marello\Bundle\ProductBundle\Entity\Product',
                'cascade_validation' => true,
                'csrf_protection'    => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
