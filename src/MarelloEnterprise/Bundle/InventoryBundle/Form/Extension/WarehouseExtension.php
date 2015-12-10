<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Extension;

use Marello\Bundle\InventoryBundle\Form\Type\WarehouseType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

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

        $builder->add('default', 'checkbox', [
            'required' => false,
            'tooltip'  => 'marello_enterprise.inventory.warehouse.delete',
        ]);
    }
}
