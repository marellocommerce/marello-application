<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Options\OptionsContext;
use Oro\Bundle\ApiBundle\Processor\NormalizeResultContext;
use Oro\Bundle\ApiBundle\Processor\RequestActionProcessor;

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
    protected function getLogContext(NormalizeResultContext $context): array
    {
        // remove id from result
        $result = parent::getLogContext($context);
        if (array_key_exists('id', $result) && empty($result['id'])) {
            unset($result['id']);
        }

        return $result;
    }
}
