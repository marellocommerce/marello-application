<?php

namespace Marello\Bundle\POSUserBundle\Api\Processor;

use Marello\Bundle\POSUserBundle\Api\Model\POSUserApi;
use Marello\Bundle\POSUserBundle\Api\Processor\Authenticate\AuthenticationContext;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

class NormalizePOSUserApi implements ProcessorInterface
{
    public function process(ContextInterface $context)
    {
        /** @var AuthenticationContext $context */
        if (!$context->hasResult() || !$context->get(LoadOroUserApi::USER_API_ENTITY_LOADED)) {
            // the entity is not loaded do not continue
            return;
        }

        if (!$context->getId()) {
            // entity doesn't exist
            return;
        }

        /** @var POSUserApi $result */
        $result = $context->getResult();
        if (!is_object($result)) {
            // data already normalized
            return;
        }

        if (!$context->getErrors()) {
            $normalizedObjectData = $result->toArray();
            $context->setResult($normalizedObjectData);
        }
    }
}
