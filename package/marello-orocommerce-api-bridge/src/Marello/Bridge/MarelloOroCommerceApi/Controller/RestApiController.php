<?php

namespace Marello\Bridge\MarelloOroCommerceApi\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Marello\Bridge\MarelloOroCommerceApi\Request\Rest\RequestHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API controller.
 */
class RestApiController
{
    /** @var RequestHandler */
    private $requestHandler;

    /**
     * @param RequestHandler $requestHandler
     */
    public function __construct(RequestHandler $requestHandler)
    {
        $this->requestHandler = $requestHandler;
    }

    /**
     * Handle a list of entities
     *
     * @param Request $request
     *
     * @ApiDoc(
     *     description="Handle a entities collection",
     *     resource=true,
     *     views={"rest_plain", "rest_json_api"}
     * )
     *
     * @return Response
     */
    public function collectionAction(Request $request): Response
    {
        return $this->requestHandler->handleCollection($request);
    }
}
