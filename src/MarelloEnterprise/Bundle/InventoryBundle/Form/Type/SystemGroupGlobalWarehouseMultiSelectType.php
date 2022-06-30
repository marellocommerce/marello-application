<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class SystemGroupGlobalWarehouseMultiSelectType extends AbstractWarehouseMultiSelectType
{
    const BLOCK_PREFIX = 'marello_system_group_global_warehouse_multi_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'autocomplete_alias' => 'system_group_global_warehouses',
            'configs'            => [
                'multiple'    => true,
                'placeholder' => 'marelloenterprise.inventory.warehouse.form.select_warehouse',
                'result_template_twig' =>
                    '@MarelloEnterpriseInventory/Warehouse/Autocomplete/result.html.twig',
                'selection_template_twig' =>
                    '@MarelloEnterpriseInventory/Warehouse/Autocomplete/selection.html.twig',
                'allowClear'  => true,
                'component' => 'autocomplete-owner-aware-warehouse-group'
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
