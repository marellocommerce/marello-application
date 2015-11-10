<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Model\WarehouseInventory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class WarehouseInventoryType extends AbstractType
{
    const NAME = 'marello_warehouse_inventory';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('modifyOperator', 'choice', [
                'choices' => [
                    WarehouseInventory::OPERATOR_INCREASE => 'Increase',
                    WarehouseInventory::OPERATOR_DECREASE => 'Decrease',
                ],
            ])
            ->add('modifyAmount', 'number');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Marello\Bundle\InventoryBundle\Model\WarehouseInventory',
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
