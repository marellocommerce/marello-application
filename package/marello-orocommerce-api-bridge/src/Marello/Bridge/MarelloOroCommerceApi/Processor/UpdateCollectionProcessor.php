<?php

namespace Marello\Bridge\MarelloOroCommerceApi\Processor;

use Marello\Bridge\MarelloOroCommerceApi\Processor\CreateCollection\CreateCollectionContext;
use Marello\Bridge\MarelloOroCommerceApi\Processor\UpdateCollection\UpdateCollectionContext;
use Oro\Bundle\ApiBundle\Processor\Create\CreateContext;
use Oro\Bundle\ApiBundle\Processor\NormalizeResultContext;
use Oro\Bundle\ApiBundle\Processor\RequestActionProcessor;
use Oro\Bundle\ApiBundle\Processor\Update\UpdateContext;
use Oro\Bundle\ApiBundle\Provider\ConfigProvider;
use Oro\Bundle\ApiBundle\Provider\MetadataProvider;
use Oro\Bundle\ApiBundle\Request\ApiActionGroup;
use Oro\Component\ChainProcessor\ContextInterface as ComponentContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * The main processor for "update_collection" action.
 */
class UpdateCollectionProcessor extends RequestActionProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function createContextObject()
    {
        return new UpdateCollectionContext($this->configProvider, $this->metadataProvider);
    }

    /**
     * {@inheritdoc}
     */
    protected function executeProcessors(ComponentContextInterface $context)
    {
        /** @var UpdateCollectionContext $context */
        /** @var UpdateContext $collectionItemContext */
        foreach ($context->getCollectionItemsContexts() as $collectionItemContext) {
            $processors = $this->processorBag->getProcessors($collectionItemContext);
            $processorId = null;
            $group = null;
            try {
                $errorsHandled = false;
                /** @var ProcessorInterface $processor */
                foreach ($processors as $processor) {
                    if ($collectionItemContext->hasErrors()) {
                        foreach($collectionItemContext->getErrors() as $error) {
                            $context->addError($error);
                        }
                        $errorsHandled = true;
                        if (ApiActionGroup::NORMALIZE_RESULT !== $group) {
                            $this->handleErrors($collectionItemContext, $processorId, $group);
                            break;
                        }
                    }
                    $processorId = $processors->getProcessorId();
                    $group = $processors->getGroup();
                    $processor->process($collectionItemContext);
                }
                if (!$errorsHandled && $collectionItemContext->hasErrors()) {
                    $this->handleErrors($collectionItemContext, $processorId, $group);
                }
            } catch (\Error $e) {
                $this->handleException(
                    new \ErrorException($e->getMessage(), $e->getCode(), E_ERROR, $e->getFile(), $e->getLine()),
                    $collectionItemContext,
                    $processorId,
                    $group
                );
            } catch (\Exception $e) {
                $this->handleException($e, $collectionItemContext, $processorId, $group);
            }
        }
    }
}
