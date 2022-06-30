<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

use Marello\Bundle\InventoryBundle\Form\Type\InventoryLevelType;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Type\InventoryLevelWarehouseSelectType;

class InventoryLevelExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [InventoryLevelType::class];
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

        $builder->add('warehouse', InventoryLevelWarehouseSelectType::class);
    }
}
