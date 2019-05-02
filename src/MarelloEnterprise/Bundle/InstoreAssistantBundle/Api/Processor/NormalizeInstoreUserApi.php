<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Model\InstoreUserApi;
use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Authenticate\AuthenticationContext;

class NormalizeInstoreUserApi implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     * @param ContextInterface $context
     */
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

        /** @var InstoreUserApi $result */
        $result = $context->getResult();
        if (is_array($result) || !is_object($result)) {
            // data already normalized
            return;
        }

        if (!$context->getErrors()) {
            $normalizedObjectData = $result->toArray();
            $context->setResult($normalizedObjectData);
        }
    }
}
