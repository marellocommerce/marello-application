<?php

namespace Marello\Bridge\MarelloOroCommerceApi\Request\Rest;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Marello\Bridge\MarelloOroCommerceApi\Processor\CreateCollection\CreateCollectionContext;
use Oro\Bundle\ApiBundle\Processor\Context;
use Oro\Bundle\ApiBundle\Processor\Create\CreateContext;
use Oro\Bundle\ApiBundle\Processor\Update\UpdateContext;
use Oro\Bundle\ApiBundle\Request\Rest\RequestActionHandler as BaseRequestActionHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncode;

class RequestActionHandler extends BaseRequestActionHandler
{
    /**
     * @var string
     */
    private $itemsAction;

    /**
     * Handles "POST /api/{entity}/collection" request,
     * that creates collection of new entities.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handleCreateCollection(Request $request): Response
    {
        $this->itemsAction = 'create';
        $processor = $this->getProcessor('create_collection');
        /** @var CreateContext $context */
        $context = $processor->createContext();
        $this->preparePrimaryContext($context, $request);

        $processor->process($context);

        return $this->buildResponse($context);
    }

    /**
     * Handles "PATCH /api/{entity}/collection" request,
     * that updates collection of entities.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handleUpdateCollection(Request $request): Response
    {
        $this->itemsAction = 'update';
        $processor = $this->getProcessor('update_collection');
        /** @var UpdateContext $context */
        $context = $processor->createContext();
        $this->preparePrimaryContext($context, $request);

        $processor->process($context);

        return $this->buildResponse($context);
    }

    /**
     * @param Context $context
     * @param Request $request
     */
    protected function preparePrimaryContext(Context $context, Request $request): void
    {
        $this->prepareContext($context, $request);
        $context->setClassName($this->getRequestParameter($request, 'entity'));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContext(Context $context, Request $request): void
    {
        parent::prepareContext($context, $request);
        $requestData = $this->getRequestData($request);
        foreach ($requestData as $collectionItemData) {
            if ($this->itemsAction === 'create') {
                $collectionItemContext = new CreateContext(
                    $context->getConfigProvider(),
                    $context->getMetadataProvider()
                );
            } else {
                $collectionItemContext = new UpdateContext(
                    $context->getConfigProvider(),
                    $context->getMetadataProvider()
                );
            }
            parent::prepareContext($collectionItemContext, $request);
            $collectionItemContext->setClassName($this->getRequestParameter($request, 'entity'));
            if ($this->itemsAction === 'update') {
                $collectionItemContext->setId($collectionItemData['data']['id']);
            }
            $collectionItemContext->setRequestData($collectionItemData);
            $collectionItemContext->setAction($this->itemsAction);
            $context->addCollectionItemContext($collectionItemContext);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function buildResponse(Context $context): Response
    {
        $result = [];
        /** @var CreateCollectionContext $context */
        foreach ($context->getCollectionItemsContexts() as $collectionItemContext) {
            $result[] = $collectionItemContext->getResult();
        }
        $context->setResult($result);
        $context->setResponseStatusCode(200);

        return parent::buildResponse($context);
    }
}
