<?php

namespace Marello\Bundle\OroCommerceBundle\Integration\Connector;

use Marello\Bundle\OroCommerceBundle\Integration\Transport\Rest\OroCommerceRestTransport;
use Marello\Bundle\OroCommerceBundle\Model\SyncState;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;

abstract class AbstractOroCommerceConnector extends AbstractConnector
{
    /**
     * @var SyncState
     */
    protected $syncState;

    /**
     * @var OroCommerceRestTransport
     */
    protected $transport;

    /**
     * @param SyncState                $syncState
     * @param ContextRegistry          $contextRegistry
     * @param LoggerStrategy           $logger
     * @param ConnectorContextMediator $contextMediator
     */
    public function __construct(
        SyncState $syncState,
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        ConnectorContextMediator $contextMediator
    ) {
        $this->syncState = $syncState;
        parent::__construct($contextRegistry, $logger, $contextMediator);
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        parent::initializeFromContext($context);
        $context->setValue('channel', $this->channel);
        $this->addLastSyncDate();
    }

    /**
     * Write last sync date to context
     */
    protected function addLastSyncDate()
    {
        $today = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->addStatusData(
            SyncState::LAST_SYNC_DATE_KEY,
            $today->format(\DateTime::ISO8601)
        );
    }

    /**
     * @return \DateTime|null
     */
    protected function getLastSyncDate()
    {
        $channel = $this->contextMediator->getChannel($this->getContext());
        return $this->syncState->getLastSyncDate($channel, $this->getType());
    }

    /**
     * {@inheritdoc}
     */
    protected function validateConfiguration()
    {
        parent::validateConfiguration();

        if (!$this->transport instanceof OroCommerceRestTransport) {
            throw new \LogicException('Option "transport" should be "OroCommerceRestTransport"');
        }
    }
}
