<?php

namespace Marello\Bundle\WebhookBundle\ImportExport\Writer;

use Marello\Bundle\WebhookBundle\Manager\WebhookProvider;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Item\ExecutionContext;
use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\ImportExport\Writer\PersistentBatchWriter;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WebhookExportWriter extends PersistentBatchWriter implements
    ContextAwareInterface
{
    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var ExecutionContext
     */
    protected $contextData;

    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $eventDispatcher,
        ContextRegistry $contextRegistry,
        protected ConnectorContextMediator $connectorContextMediator,
        protected TransportInterface $transport,
        protected WebhookProvider $webhookProvider,
        LoggerInterface $logger
    ) {
        parent::__construct($registry, $eventDispatcher, $contextRegistry, $logger);
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
        $this->contextData = $stepExecution->getJobExecution()->getExecutionContext();
        $this->setImportExportContext($this->contextRegistry->getByStepExecution($stepExecution));
    }

    public function setImportExportContext(ContextInterface $context)
    {
        $this->context = $context;
    }

    public function write(array $items)
    {
        try {
            $integration = $this->webhookProvider->getWebhookIntergrationById($this->contextData->get('channel'));
            $webhookEntity = $this->webhookProvider->getWebhookById($this->contextData->get('webhook_id'));
            $this->transport->setWebhook($webhookEntity);
            $this->transport = $this->connectorContextMediator->getInitializedTransport($integration);
        } catch (\LogicException $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }

        $importContext = $this->contextRegistry->getByStepExecution($this->stepExecution);

        foreach ($items as $item) {
            try {
                $response  = $this->transport->sendRequest($item);
                if ($response instanceof RestResponseInterface) {
                    if (!in_array($response->getStatusCode(), [200,201,202])) {
                        $errorMessage = "Did not receive correct response. #Data : "
                                        . json_encode($item). ' #URL: '
                                        . $this->transport->getWebhook()->getCallbackUrl();
                        throw new \RuntimeException($errorMessage);
                    }
                }
                $importContext->incrementUpdateCount();
            } catch (\Exception $e) {
                //increment
                $importContext->incrementErrorEntriesCount();
                $importContext->incrementUpdateCount();
                throw $e;
            }
        }
    }
}
