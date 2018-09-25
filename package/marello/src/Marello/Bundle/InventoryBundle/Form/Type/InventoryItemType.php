<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class InventoryItemType extends AbstractType
{
    const NAME = 'marello_inventory_item';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'replenishment',
                EnumChoiceType::class,
                [
                    'enum_code' => 'marello_inv_reple',
                    'required'  => true,
                    'label'     => 'marello.inventory.inventoryitem.replenishment.label',
                ]
            )
            ->add(
                'desiredInventory',
                NumberType::class,
                [
                    'constraints' => new GreaterThanOrEqual(0)
                ]
            )
            ->add(
                'purchaseInventory',
                NumberType::class,
                [
                    'constraints' => new GreaterThanOrEqual(0)
                ]
            )
            ->add(
                'inventoryLevels',
                InventoryLevelCollectionType::class
            );
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
