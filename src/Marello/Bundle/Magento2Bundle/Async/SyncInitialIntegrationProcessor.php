<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Doctrine\DBAL\Exception\RetryableException;
use Marello\Bundle\Magento2Bundle\Integration\SyncProcessor\InitialSyncProcessor;
use Oro\Bundle\IntegrationBundle\Authentication\Token\IntegrationTokenAwareTrait;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SyncInitialIntegrationProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface,
    LoggerAwareInterface
{
    use IntegrationTokenAwareTrait;
    use LoggerAwareTrait;

    /** @var JobRunner */
    protected $jobRunner;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var InitialSyncProcessor */
    protected $initialSyncProcessor;

    /**
     * @param JobRunner $jobRunner
     * @param ManagerRegistry $managerRegistry
     * @param TokenStorageInterface $tokenStorage
     * @param InitialSyncProcessor $initialSyncProcessor
     */
    public function __construct(
        JobRunner $jobRunner,
        ManagerRegistry $managerRegistry,
        TokenStorageInterface $tokenStorage,
        InitialSyncProcessor $initialSyncProcessor
    ) {
        $this->jobRunner = $jobRunner;
        $this->managerRegistry = $managerRegistry;
        $this->tokenStorage = $tokenStorage;
        $this->initialSyncProcessor = $initialSyncProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $context = [];
        try {
            $wrappedMessage = SyncInitialIntegrationMessage::createFromMessage($message);
            $context = $wrappedMessage->getContextParams();

            $integration = $this->getIntegration($wrappedMessage);
            if (null === $integration) {
                $this->logger->error(
                    "[Magento 2] Can't start initial synchronization. Integration has not found.",
                    [
                        'integration_id' => $wrappedMessage->getIntegrationId()
                    ]
                );
            }

            if (false === $integration->isEnabled()) {
                $this->logger->warning(
                    "[Magento 2] Can't start initial synchronization. Integration is disable. ",
                    [
                        'integration_id' => $wrappedMessage->getIntegrationId()
                    ]
                );
            }

            $jobName = sprintf(
                '%s:%s',
                'marello_magento2:sync_initial_integration',
                $wrappedMessage->getIntegrationId()
            );

            $this->setTemporaryIntegrationToken($integration);
            $result = $this->jobRunner->runUnique(
                $message->getMessageId(),
                $jobName,
                function (JobRunner $jobRunner) use ($integration, $wrappedMessage) {
                    return $this->initialSyncProcessor->process(
                        $integration,
                        null,
                        $wrappedMessage->getConnectorParameters()
                    );
                }
            );
        } catch (\Throwable $exception) {
            $context['exception'] = $exception;

            $this->logger->critical(
                '[Magento 2] Initial synchronization failed.',
                $context
            );

            if ($exception instanceof RetryableException) {
                return self::REQUEUE;
            }

            return self::REJECT;
        }

        /**
         * Requeue in case when same unique job already running
         */
        return $result ? self::ACK : self::REQUEUE;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::SYNC_INITIAL_INTEGRATION];
    }

    /**
     * @param SyncInitialIntegrationMessage $syncInitialIntegrationMessage
     * @return Integration|null
     */
    protected function getIntegration(SyncInitialIntegrationMessage $syncInitialIntegrationMessage): ?Integration
    {
        return $this->managerRegistry
            ->getManagerForClass(Integration::class)
            ->getRepository(Integration::class)
            ->find($syncInitialIntegrationMessage->getIntegrationId());
    }
}
