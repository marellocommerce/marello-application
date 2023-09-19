<?php

namespace Marello\Bundle\POSUserBundle\Api\Handler;

use Marello\Bundle\POSUserBundle\Api\Model\POSUserApi;
use Marello\Bundle\POSUserBundle\Api\Processor\Authenticate\AuthenticationContext;
use Oro\Bundle\ApiBundle\Request\Rest\RequestActionHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class POSUserRequestActionHandler extends RequestActionHandler
{
    public function handleAuthenticate(Request $request): Response
    {
        $processor = $this->getProcessor('authenticate');
        /** @var AuthenticationContext $context */
        $context = $processor->createContext();
        $this->preparePrimaryContext($context, $request);
        $context->setClassName(POSUserApi::class);
        $context->setRequestData($request->request->all());

        $processor->process($context);

        return $this->buildResponse($context);
    }
}
