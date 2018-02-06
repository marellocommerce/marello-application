<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Options;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

class AddCorsRequestHeaders implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     * @param ContextInterface $context
     */
    public function process(ContextInterface $context)
    {
        /** @var OptionsContext $context */
    }
}
