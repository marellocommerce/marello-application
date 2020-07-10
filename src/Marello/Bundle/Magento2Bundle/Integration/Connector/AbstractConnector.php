<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Connector;

use Marello\Bundle\Magento2Bundle\Exception\InvalidConfigurationException;
use Marello\Bundle\Magento2Bundle\Transport\RestTransport;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector as AbstractParentConnector;

abstract class AbstractConnector extends AbstractParentConnector
{
    /**
     * @var RestTransport
     */
    protected $transport;

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        parent::initializeFromContext($context);
        $context->setValue('channel', $this->channel);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateConfiguration()
    {
        if (!$this->transport instanceof RestTransport) {
            throw new InvalidConfigurationException('Option "transport" must be instance of "RestTransport".');
        }
    }
}
