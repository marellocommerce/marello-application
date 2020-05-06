<?php

namespace Marello\Bridge\MarelloOroCommerceApi\Processor\UpdateCollection;

use Oro\Bundle\ApiBundle\Processor\Context;
use Oro\Bundle\ApiBundle\Processor\Create\CreateContext;
use Oro\Bundle\ApiBundle\Processor\Update\UpdateContext;
use Oro\Bundle\ApiBundle\Provider\ConfigProvider;
use Oro\Bundle\ApiBundle\Provider\MetadataProvider;

/**
 * The execution context for processors for "update_collection" action.
 */
class UpdateCollectionContext extends Context
{
    /**
     * @var UpdateContext[]
     */
    private $collectionItemsContexts = [];

    /**
     * @return ConfigProvider
     */
    public function getConfigProvider(): ConfigProvider
    {
        return $this->configProvider;
    }

    /**
     * @return MetadataProvider
     */
    public function getMetadataProvider(): MetadataProvider
    {
        return $this->metadataProvider;
    }

    /**
     * @return UpdateContext[]
     */
    public function getCollectionItemsContexts(): array
    {
        return $this->collectionItemsContexts;
    }

    public function addCollectionItemContext(UpdateContext $context)
    {
        $this->collectionItemsContexts[] = $context;
    }
}
