<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotLinkedWarehouseGroupAutocompleteType extends AbstractType
{
    const NAME = 'marello_not_linked_warehousegroup_autocomplete';
    const AUTOCOMPLETE_ALIAS = 'not_linked_warehouse_groups';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroEntitySelectOrCreateInlineType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => self::AUTOCOMPLETE_ALIAS,
                'grid_name' => 'marelloenterprise-inventory-not-linked-warehousegroups-grid',
                'create_form_route' => 'marelloenterprise_inventory_warehousegroup_create',
                'configs' => [
                    'placeholder' => 'marelloenterprise.inventory.warehousegroup.form.select_warehousegroup',
                    'allowClear'  => true,
                    'component' => 'autocomplete-owner-aware-warehouse-group',
                ],
            ]
        );
    }
}
