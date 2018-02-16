<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Marello\Bundle\OroCommerceBundle\Integration\Transport\Rest\OroCommerceRestTransport;
use Oro\Bundle\EntityBundle\ORM\Registry;
use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractExportWriter implements
    ItemWriterInterface,
    StepExecutionAwareInterface,
    ContextAwareInterface
{
    const ACTION_FIELD = 'action';
    
    const CREATE_ACTION = 'create';
    const UPDATE_ACTION = 'update';
    const DELETE_ACTION = 'delete';
    
    /**
     * @var Registry
     */
    protected $registry;
    
    /**
     * @var OroCommerceRestTransport
     */
    protected $transport;

    /**
     * @var ConnectorContextMediator
     */
    protected $connectorContextMediator;

    /**
     * @var ContextRegistry
     */
    protected $contextRegistry;

    /**
     * @var LoggerStrategy
     */
    protected $logger;

    /**
     * @var Channel
     */
    protected $channel = null;

    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @param Registry $registry
     * @param ContextRegistry $contextRegistry
     * @param ConnectorContextMediator $connectorContextMediator
     * @param LoggerStrategy $logger
     * @param OroCommerceRestTransport $transport
     */
    public function __construct(
        Registry $registry,
        ContextRegistry $contextRegistry,
        ConnectorContextMediator $connectorContextMediator,
        LoggerStrategy $logger,
        OroCommerceRestTransport $transport
    ) {
        $this->registry = $registry;
        $this->contextRegistry = $contextRegistry;
        $this->connectorContextMediator = $connectorContextMediator;
        $this->logger = $logger;
        $this->transport = $transport;
    }

    /**
     * @param array $entities
     * @throws \Exception
     */
    public function write(array $entities)
    {
        $this->transport->init($this->getChannel()->getTransport());

        foreach ($entities as $entity) {
            $this->writeItem($entity);
        }
    }

    /**
     * @param array $data
     */
    abstract protected function writeItem(array $data);

    /**
     * @return Channel
     */
    protected function getChannel()
    {
        if ($this->channel === null  || $this->channel->getId() !== (int)$this->context->getOption('channel')) {
            $this->channel = $this->connectorContextMediator->getChannel($this->context);
        }

        return $this->channel;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->setImportExportContext($this->contextRegistry->getByStepExecution($stepExecution));
    }

    /**
     * {@inheritdoc}
     */
    public function setImportExportContext(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * @param ParameterBag $response
     * @return bool
     */
    protected function checkErrors(ParameterBag $response)
    {
        if ($response->get('Ack') === 'Failure' && $response->get('Errors')) {
            $errors = (array)$response->get('Errors');
            if (isset($errors['LongMessage'])) {
                $this->context->addError($errors['LongMessage']);
            } elseif (isset($errors['ErrorCode'])) {
                $this->context->addError(sprintf('Error Code #%s', $errors['ErrorCode']));
            }

            return true;
        }
        
        return false;
    }
}
