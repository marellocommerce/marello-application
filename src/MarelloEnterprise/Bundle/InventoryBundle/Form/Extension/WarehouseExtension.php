<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

use Marello\Bundle\InventoryBundle\Form\Type\WarehouseType;

class WarehouseExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return WarehouseType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('default', 'checkbox', [
                'required' => false,
                'tooltip'  => 'marelloenterprise.inventory.warehouse.delete',
            ])
            ->add(
                'warehouseType', 'entity', [
                'label'    => 'marello.inventory.warehouse.warehousetype.entity_label',
                'class'    => 'MarelloInventoryBundle:WarehouseType',
                'property' => 'label',
                'required' => true,
            ]);
    }
}
