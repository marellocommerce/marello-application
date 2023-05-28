<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class ExpectedInventoryForAllocationItemsGrid
{
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $columns = $config->offsetGetByPath('[columns]', []);
        $columns['expectedInventory'] = [
            'label' => 'marello.inventory.inventorylevel.expected_inventory.label',
            'type' => 'twig',
            'frontend_type' => 'html',
            'template' => '@MarelloEnterpriseInventory/Datagrid/Column/expectedInventory.html.twig',
        ];
        $config->offsetSetByPath('[columns]', $columns);
    }
}
