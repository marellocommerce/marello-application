<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\Reader;

use Oro\Bundle\EntityBundle\ORM\Registry;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;

class InventoryLevelReader extends AbstractInventoryLevelReader
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param ContextRegistry $contextRegistry,
     * @param Registry $registry
     */
    public function __construct(ContextRegistry $contextRegistry, Registry $registry)
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
