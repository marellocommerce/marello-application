<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Controller\Api;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Handler\InstoreAssistantRequestActionHandler;
use Oro\Bundle\ApiBundle\Controller\RestApiController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsOptionsController extends RestApiController
{
    /**
     * {@inheritdoc}
     * @param Request $request
     *
     * @return Response
     */
    public function optionsAction(Request $request)
    {
        return $this->getHandler()->handleOptions($request);
    }

    /**
     * @return InstoreAssistantRequestActionHandler
     */
    private function getHandler(): InstoreAssistantRequestActionHandler
    {
        return $this->get('marelloenterprise_instoreassistant.api.handler.request_action_handler');
    }
}
