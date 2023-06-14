<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsolidationEnabledType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_inventory_consolidation_enabled';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'label' => 'marello.order.consolidation.label',
                'dynamic_fields_ignore_exception' => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CheckboxType::class;
    }
}
