<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor;

use Oro\Component\ChainProcessor\ContextInterface as ComponentContextInterface;
use Oro\Bundle\ApiBundle\Processor\RequestActionProcessor;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Options\OptionsContext;

class CorsOptionsProcessor extends RequestActionProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function createContextObject()
    {
        return new OptionsContext($this->configProvider, $this->metadataProvider);
    }

    /**
     * {@inheritdoc}
     */
    protected function getLogContext(ComponentContextInterface $context)
    {
        // remove id from result
        $result = parent::getLogContext($context);
        if (array_key_exists('id', $result) && empty($result['id'])) {
            unset($result['id']);
        }

        return $result;
    }
}
