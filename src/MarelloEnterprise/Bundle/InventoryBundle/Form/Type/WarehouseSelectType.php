<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WarehouseSelectType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_warehouse_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'warehouses',
                'entity_class' => Warehouse::class,
                'configs'            => [
                    'placeholder' => 'marelloenterprise.inventory.warehouse.form.select_warehouse',
                    'result_template_twig' =>
                        '@MarelloEnterpriseInventory/Warehouse/Autocomplete/result.html.twig',
                    'selection_template_twig' =>
                        '@MarelloEnterpriseInventory/Warehouse/Autocomplete/selection.html.twig',
                ],
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
        return OroJquerySelect2HiddenType::class;
    }
}
