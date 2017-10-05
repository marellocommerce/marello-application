<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class SystemGroupGlobalWarehouseMultiSelectType extends AbstractWarehouseMultiSelectType
{
    const NAME = 'marello_system_group_global_warehouse_multi_select';

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
                    'MarelloEnterpriseInventoryBundle:Warehouse:Autocomplete/result.html.twig',
                'selection_template_twig' =>
                    'MarelloEnterpriseInventoryBundle:Warehouse:Autocomplete/selection.html.twig',
                'allowClear'  => true,
                'component' => 'autocomplete-owner-aware-warehouse-group'
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
