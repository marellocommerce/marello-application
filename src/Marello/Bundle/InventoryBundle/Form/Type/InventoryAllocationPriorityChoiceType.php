<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\FormBundle\Form\Type\OroChoiceType;

class InventoryAllocationPriorityChoiceType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_inventory_allocation_priority_choice';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choices' => [
                    'marello.inventory.system_configuration.fields.inventory_allocation_priority.mixed' => 0,
                    'marello.inventory.system_configuration.fields.inventory_allocation_priority.internal' => 1,
                    'marello.inventory.system_configuration.fields.inventory_allocation_priority.external' => 2,
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
