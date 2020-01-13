<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\Reader;

use Doctrine\Common\Persistence\ManagerRegistry;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;

class InventoryLevelReader extends AbstractInventoryLevelReader
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ContextRegistry $contextRegistry,
     * @param ManagerRegistry $registry
     */
    public function __construct(ContextRegistry $contextRegistry, ManagerRegistry $registry)
    {
        parent::__construct($contextRegistry);
        $this->registry = $registry;
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

