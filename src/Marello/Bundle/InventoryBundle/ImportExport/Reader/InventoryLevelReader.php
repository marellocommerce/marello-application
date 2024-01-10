<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\Reader;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;

class InventoryLevelReader extends AbstractInventoryLevelReader
{
    public function __construct(
        ContextRegistry $contextRegistry,
        protected ManagerRegistry $registry
    ) {
        parent::__construct($contextRegistry);
    }

    /**
     * @return array
     */
    protected function getInventoryLevels()
    {
        return $this->registry
            ->getManagerForClass(InventoryLevel::class)
            ->getRepository(InventoryLevel::class)
            ->findAll();
    }
}
