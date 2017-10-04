<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class WarehouseMultiSelectType extends AbstractWarehouseMultiSelectType
{
    const NAME = 'marello_warehouse_multi_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'autocomplete_alias' => 'warehouses',
            'configs'            => [
                'multiple'    => true,
                'placeholder' => 'marelloenterprise.inventory.warehouse.form.select_warehouse',
                'result_template_twig' =>
                    'MarelloEnterpriseInventoryBundle:Warehouse:Autocomplete/result.html.twig',
                'selection_template_twig' =>
                    'MarelloEnterpriseInventoryBundle:Warehouse:Autocomplete/selection.html.twig',
                'allowClear'  => true
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
