<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\Reader;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateManager;

class InventoryLevelTemplateReader extends AbstractInventoryLevelReader
{
    /**
     * @var TemplateManager
     */
    protected $templateManager;

    /**
     * @param ContextRegistry $contextRegistry,
     * @param TemplateManager $templateManager
     */
    public function __construct(ContextRegistry $contextRegistry, TemplateManager $templateManager)
    {
        parent::__construct($contextRegistry);
        $this->templateManager = $templateManager;
    }

    /**
     * @return array
     */
    protected function getInventoryLevels()
    {
        return $this->templateManager->getEntityFixture(InventoryLevel::class)->getData();
    }
}
