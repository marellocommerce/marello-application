<?php

namespace Marello\Bridge\MarelloOroCommerceApi\Request\Rest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles REST API requests.
 */
class RequestHandler
{
    /**
     * @var RequestActionHandler
     */
    private $actionHandler;

    /**
     * @param RequestActionHandler $actionHandler
     */
    public function __construct(RequestActionHandler $actionHandler)
    {
        $this->actionHandler = $actionHandler;
    }

    /**
     * Handles "/api/{entity}/collection" requests.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handleCollection(Request $request): Response
    {
        switch ($request->getMethod()) {
            case Request::METHOD_POST:
                return $this->actionHandler->handleCreateCollection($request);
            case Request::METHOD_PATCH:
                return $this->actionHandler->handleUpdateCollection($request);
        }

        return $this->actionHandler->handleNotAllowedItem($request);
    }
}
