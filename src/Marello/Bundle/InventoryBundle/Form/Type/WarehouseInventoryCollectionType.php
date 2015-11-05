<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WarehouseInventoryCollectionType extends AbstractType
{
    const NAME = 'marello_warehouse_inventory_collection';

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_add'    => false,
            'allow_delete' => false,
            'type'         => WarehouseInventoryType::NAME,
        ]);
    }

    public function getParent()
    {
        return 'collection';
    }

    public function getName()
    {
        return self::NAME;
    }
}
