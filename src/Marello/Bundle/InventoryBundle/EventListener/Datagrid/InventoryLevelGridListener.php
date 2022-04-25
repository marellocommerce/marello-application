<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Datagrid;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class InventoryLevelGridListener
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $helper
     */
    public function __construct(DoctrineHelper $helper)
    {
        $this->doctrineHelper = $helper;
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $inventoryItemId = $this->getParameter($event->getDatagrid(), 'inventoryItemId');
        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $this->doctrineHelper
            ->getEntityRepositoryForClass(InventoryItem::class)
            ->find($inventoryItemId);
        if ($inventoryItem->isEnableBatchInventory()) {
            $config = $event->getConfig();
            $columns = $config->offsetGetByPath('[columns]');
            $columns = array_merge(
                $columns,
                [
                    'manageBatches' => [
                        'label' => 'marello.inventory.inventorylevel.grid.batches.label',
                        'type' => 'twig',
                        'frontend_type' => 'html',
                        'template' => '@MarelloInventory/Inventory/Datagrid/manageBatches.html.twig',
                    ]
                ]
            );
            $config->offsetSetByPath('[columns]', $columns);
        }
    }

    /**
     * @param DatagridInterface $datagrid
     * @param string $parameterName
     * @return mixed
     */
    protected function getParameter(DatagridInterface $datagrid, $parameterName)
    {
        $value = $datagrid->getParameters()->get($parameterName);

        if ($value === null) {
            throw new \LogicException(sprintf('Parameter "%s" must be set', $parameterName));
        }

        return $value;
    }
}
