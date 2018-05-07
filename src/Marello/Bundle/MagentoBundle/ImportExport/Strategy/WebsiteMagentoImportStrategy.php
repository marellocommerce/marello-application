<?php

namespace Marello\Bundle\MagentoBundle\ImportExport\Strategy;

use Marello\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\DoctrineHelper;
use Marello\Bundle\MagentoBundle\Provider\Connector\MagentoConnectorInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class WebsiteMagentoImportStrategy extends AbstractImportStrategy
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function setDoctrineHelper($doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function processEntity(
        $entity,
        $isFullData = false,
        $isPersistNew = false,
        $itemData = null,
        array $searchContext = [],
        $entityIsRelation = false
    ) {
        $excluded = [];

        $entity = parent::processEntity(
            $entity,
            $isFullData,
            $isPersistNew,
            $itemData,
            $searchContext,
            $entityIsRelation
        );

        return $this->doctrineHelper
            ->findAndReplaceEntity($entity, MagentoConnectorInterface::WEBSITE_TYPE, 'id', $excluded);
    }
}
