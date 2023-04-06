<?php

namespace Marello\Bundle\WebhookBundle\Integration\Connector;

use Marello\Bundle\WebhookBundle\Entity\Webhook;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
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
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getConnectorSource()
    {
        $executionContext = $this->stepExecution
            ->getJobExecution()
            ->getExecutionContext();


        $iterator = new \AppendIterator;
        $iterator->append(new \ArrayIterator([]));
        return $iterator;
    }

    /**
     * {@inheritdoc}
     */
//    protected function initializeFromContext(ContextInterface $context)
//    {
//        //quick fix avoiding empty context
//        $jobExecutionContext = $this->getStepExecution()->getJobExecution()->getExecutionContext();
//        $jobExecutionContext->clearDirtyFlag();
//        foreach ($jobExecutionContext->getKeys() as $key) {
//            $context->removeOption($key);
//            $context->setValue($key, $jobExecutionContext->get('channel'));
//        }
//        parent::initializeFromContext($context);
//    }


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
}
