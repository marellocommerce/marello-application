<?php

namespace Marello\Bundle\WebhookBundle\Integration\Connector;

use Marello\Bundle\WebhookBundle\Entity\Webhook;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;

class WebhookNotificationConnector extends AbstractConnector
{
    public const EXPORT_JOB_NAME = 'marello_webhook_notification';
    public const TYPE = 'notification';

    /**
     * @param ContextRegistry $contextRegistry
     * @param LoggerStrategy $logger
     * @param ConnectorContextMediator $contextMediator
     */
    public function __construct(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        ConnectorContextMediator $contextMediator
    ) {
        parent::__construct($contextRegistry, $logger, $contextMediator);
        $this->logger          = $logger;
        $this->contextMediator = $contextMediator;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return 'marello.webhook.connector.notification.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN(): string
    {
        return Webhook::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName(): string
    {
        return self::EXPORT_JOB_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return new \ArrayIterator();
    }
}
