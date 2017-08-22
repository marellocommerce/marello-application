<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

use Marello\Bundle\InventoryBundle\Form\Type\InventoryLevelType;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseSelectType;

class InventoryLevelExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return InventoryLevelType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if ($builder->has('warehouse')) {
            $builder->remove('warehouse');
        }

        $builder->add('warehouse', WarehouseSelectType::NAME);
    }
}
