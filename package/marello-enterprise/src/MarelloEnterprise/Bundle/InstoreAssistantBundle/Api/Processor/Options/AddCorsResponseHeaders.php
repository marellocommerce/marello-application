<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Options;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

class AddCorsResponseHeaders implements ProcessorInterface
{
    const RESPONSE_HEADER_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
    const RESPONSE_HEADER_ALLOW_METHODS = 'Access-Control-Allow-Methods';
    const RESPONSE_HEADER_ALLOW_HEADERS = 'Access-Control-Allow-Headers';
    const RESPONSE_HEADER_MAX_AGE = 'Access-Control-Max-Age';

    /** CorsRequestHeaders $corsRequestHeaders */
    protected $corsRequestHeaders;

    /**
     * {@inheritdoc}
     * @param ContextInterface $context
     */
    public function process(ContextInterface $context)
    {
        /** @var OptionsContext $context */
        if (!$context->getErrors()) {
            $context->getResponseHeaders()->set(self::RESPONSE_HEADER_ALLOW_ORIGIN, '*');
        }
    }
}
