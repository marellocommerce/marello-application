<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Handler;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Model\InstoreUserApi;
use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Authenticate\AuthenticationContext;
use Oro\Bundle\ApiBundle\Request\Rest\RequestActionHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InstoreAssistantRequestActionHandler extends RequestActionHandler
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function handleAuthenticate(Request $request): Response
    {
        $processor = $this->getProcessor('authenticate');
        /** @var AuthenticationContext $context */
        $context = $processor->createContext();
        $this->preparePrimaryContext($context, $request);
        $context->setClassName(InstoreUserApi::class);
        $context->setRequestData($request->request->all());

        $processor->process($context);

        return $this->buildResponse($context);
    }
}
