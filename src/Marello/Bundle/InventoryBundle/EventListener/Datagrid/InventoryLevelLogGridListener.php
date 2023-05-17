<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Datagrid;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;

class InventoryLevelLogGridListener
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
        $config = $event->getConfig();
        $columns = $config->offsetGetOr('columns', []);
        $inventoryItemId = $this->getParameter($event->getDatagrid(), 'inventoryItemId');
        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $this->doctrineHelper
            ->getEntityRepositoryForClass(InventoryItem::class)
            ->find($inventoryItemId);
        if ($inventoryItem->isEnableBatchInventory()) {
            $inventoryBatchColumn = [
                'inventoryBatch' => [
                    'data_name' => 'inventoryBatch',
                    'frontend_type' => 'string',
                    'order' => 20
                ]
            ];

            $columns = array_merge($inventoryBatchColumn, $columns);
            $config->offsetSet('columns', $columns);
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
