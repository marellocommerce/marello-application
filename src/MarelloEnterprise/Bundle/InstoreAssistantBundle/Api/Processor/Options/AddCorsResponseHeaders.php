<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Options;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\ApiBundle\Request\Constraint;
use Oro\Bundle\ApiBundle\Model\Error;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Model\InstoreUserApi;
use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Authenticate\AuthenticationContext;

class AddCorsResponseHeaders implements ProcessorInterface
{
    const RESPONSE_HEADER_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
    const RESPONSE_HEADER_ALLOW_METHODS = 'Access-Control-Allow-Methods';
    const RESPONSE_HEADER_ALLOW_HEADERS = 'Access-Control-Allow-Headers';
    const RESPONSE_HEADER_MAX_AGE = 'Access-Control-Max-Age';

    /**
     * {@inheritdoc}
     * @param ContextInterface $context
     */
    public function process(ContextInterface $context)
    {
        /** @var OptionsContext $context */
        $context->removeResult();
        $context->getResponseHeaders()->set(self::RESPONSE_HEADER_ALLOW_ORIGIN, '*');
        $context->getResponseHeaders()->set(self::RESPONSE_HEADER_ALLOW_METHODS, 'GET, POST, OPTIONS');
        $context->getResponseHeaders()->set(self::RESPONSE_HEADER_ALLOW_HEADERS, 'Authorization,X-WSSE,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,Cache-Control,Content-Type');
        $context->getResponseHeaders()->set(self::RESPONSE_HEADER_MAX_AGE, 1728000);
        $context->setResponseStatusCode(200);
    }
}
