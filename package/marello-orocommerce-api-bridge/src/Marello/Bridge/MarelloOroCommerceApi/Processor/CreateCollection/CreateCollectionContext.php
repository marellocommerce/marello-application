<?php

namespace Marello\Bridge\MarelloOroCommerceApi\Processor\CreateCollection;

use Oro\Bundle\ApiBundle\Processor\Context;
use Oro\Bundle\ApiBundle\Processor\Create\CreateContext;
use Oro\Bundle\ApiBundle\Provider\ConfigProvider;
use Oro\Bundle\ApiBundle\Provider\MetadataProvider;

/**
 * The execution context for processors for "create_collection" action.
 */
class CreateCollectionContext extends Context
{
    /**
     * @var CreateContext[]
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
     * @return CreateContext[]
     */
    public function getCollectionItemsContexts(): array
    {
        return $this->collectionItemsContexts;
    }

    public function addCollectionItemContext(CreateContext $context)
    {
        $this->collectionItemsContexts[] = $context;
    }
}
