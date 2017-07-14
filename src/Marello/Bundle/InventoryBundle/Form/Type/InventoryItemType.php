<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Form\Type\InventoryLevelCollectionType;

class InventoryItemType extends AbstractType
{
    const NAME = 'marello_inventory_item';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('replenishment', 'oro_enum_choice',
                [
                    'enum_code' => 'marello_product_reple',
                    'required'  => true,
                    'label'     => 'marello.product.replenishment.label',
                ]
            )
            ->add('inventoryLevels', InventoryLevelCollectionType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => InventoryItem::class,
            'cascade_validation' => true,
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