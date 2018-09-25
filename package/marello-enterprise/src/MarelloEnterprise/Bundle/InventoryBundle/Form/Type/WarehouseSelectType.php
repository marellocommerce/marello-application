<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WarehouseSelectType extends AbstractType
{
    const NAME = 'marello_warehouse_select';

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
                        'MarelloEnterpriseInventoryBundle:Warehouse:Autocomplete/result.html.twig',
                    'selection_template_twig' =>
                        'MarelloEnterpriseInventoryBundle:Warehouse:Autocomplete/selection.html.twig',
                ],
            ]
        );
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

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_jqueryselect2_hidden';
    }
}
