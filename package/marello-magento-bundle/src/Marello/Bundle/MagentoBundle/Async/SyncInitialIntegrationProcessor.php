<?php

namespace Marello\Bundle\MagentoBundle\Async;

use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

//use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Authentication\Token\IntegrationTokenAwareTrait;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Marello\Bundle\MagentoBundle\Provider\InitialSyncProcessor;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;
use Oro\Bundle\SearchBundle\Engine\IndexerInterface;

use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;

class SyncInitialIntegrationProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    use IntegrationTokenAwareTrait;

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var InitialSyncProcessor
     */
    private $initialSyncProcessor;

    /**
     * @var OptionalListenerManager
     */
    private $optionalListenerManager;

    /**
     * @var JobRunner
     */
    private $jobRunner;

    /**
     * @var IndexerInterface
     */
    private $indexer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param InitialSyncProcessor $initialSyncProcessor
     * @param OptionalListenerManager $optionalListenerManager
     * @param JobRunner $jobRunner
     * @param IndexerInterface $indexer
     * @param TokenStorageInterface $tokenStorage
     * @param LoggerInterface $logger
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        InitialSyncProcessor $initialSyncProcessor,
        OptionalListenerManager $optionalListenerManager,
        JobRunner $jobRunner,
        IndexerInterface $indexer,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $logger
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->initialSyncProcessor = $initialSyncProcessor;
        $this->optionalListenerManager = $optionalListenerManager;
        $this->jobRunner = $jobRunner;
        $this->indexer = $indexer;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
        $this->initialSyncProcessor->getLoggerStrategy()->setLogger($this->logger);
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $body = JSON::decode($message->getBody());
        $body = array_replace_recursive([
            'integration_id' => null,
            'connector' => null,
            'connector_parameters' => [],
        ], $body);

        if (false == $body['integration_id']) {
            $this->logger->critical('The message invalid. It must have integrationId set');

            return self::REJECT;
        }

        $jobName = 'marello_magento:sync_initial_integration:'.$body['integration_id'];
        $ownerId = $message->getMessageId();

        /** @var EntityManagerInterface $em */
        $em = $this->doctrineHelper->getEntityManager(Integration::class);
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        /** @var Integration $integration */
        $integration = $em->find(Integration::class, $body['integration_id']);
        if (!$integration) {
            $this->logger->error(
                sprintf('Integration not found: %s', $body['integration_id'])
            );

            return self::REJECT;
        }
        if (! $integration->isEnabled()) {
            $this->logger->error(
                sprintf('Integration is not enabled: %s', $body['integration_id'])
            );

            return self::REJECT;
        }

        $result = $this->jobRunner->runUnique($ownerId, $jobName, function () use ($body, $integration) {
            $enabledListeners = [];
            $disabledListeners = [];

            $this->changeListenersStatus($enabledListeners, $disabledListeners);

            $this->setTemporaryIntegrationToken($integration);

            $result = $this->initialSyncProcessor->process(
                $integration,
                $body['connector'],
                $body['connector_parameters']
            );

            $this->changeListenersStatus($disabledListeners, $enabledListeners);

            return $result;
        });

        return $result ? self::ACK : self::REJECT;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::SYNC_INITIAL_INTEGRATION];
    }

    /**
     * @param array $disableListeners
     * @param array $enableListeners
     */
    private function changeListenersStatus(array $disableListeners, array $enableListeners = [])
    {
        $knownListeners = $this->optionalListenerManager->getListeners();

        foreach ($disableListeners as $listenerId) {
            if (in_array($listenerId, $knownListeners, true)) {
                $this->optionalListenerManager->disableListener($listenerId);
            }
        }

        foreach ($enableListeners as $listenerId) {
            if (in_array($listenerId, $knownListeners, true)) {
                $this->optionalListenerManager->enableListener($listenerId);
            }
        }
    }
}
