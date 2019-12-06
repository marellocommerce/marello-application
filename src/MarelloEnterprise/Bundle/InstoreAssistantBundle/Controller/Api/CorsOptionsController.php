<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Controller\Api;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Handler\InstoreAssistantRequestActionHandler;
use Oro\Bundle\ApiBundle\Controller\RestApiController;
use Oro\Bundle\ApiBundle\Request\Rest\RequestHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsOptionsController extends RestApiController
{
    /**
     * @var InstoreAssistantRequestActionHandler
     */
    private $requestActionHandler;

    /**
     * @param RequestHandler $requestHandler
     * @param InstoreAssistantRequestActionHandler $requestActionHandler
     */
    public function __construct(
        RequestHandler $requestHandler,
        InstoreAssistantRequestActionHandler $requestActionHandler
    ) {
        parent::__construct($requestHandler);
        $this->requestActionHandler = $requestActionHandler;
    }
    
    /**
     * {@inheritdoc}
     * @param Request $request
     *
     * @return Response
     */
    public function optionsAction(Request $request)
    {
        return $this->requestActionHandler->handleOptionsList($request);
    }
}
