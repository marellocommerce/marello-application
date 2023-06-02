<?php
namespace Marello\Bundle\WebhookBundle\Async;

use Marello\Bundle\WebhookBundle\Integration\Connector\WebhookNotificationConnector;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Authentication\Token\IntegrationTokenAwareTrait;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ReverseSyncProcessor;
use Oro\Bundle\MessageQueueBundle\Consumption\Extension\RedeliveryMessageExtension;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Async reverse processor to run reverse integration processor.
 */
class WebhookSyncProcessor implements
    MessageProcessorInterface,
    ContainerAwareInterface,
    TopicSubscriberInterface
{
    use ContainerAwareTrait;
    use IntegrationTokenAwareTrait;

    /** @var DoctrineHelper  */
    private DoctrineHelper $doctrineHelper;

    /**
     * @var ReverseSyncProcessor
     */
    private ReverseSyncProcessor $reverseSyncProcessor;

    /** @var JobRunner */
    private JobRunner $jobRunner;

    /** @var ConfigManager  */
    private ConfigManager $configManager;

    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ReverseSyncProcessor $reverseSyncProcessor
     * @param JobRunner $jobRunner
     * @param TokenStorageInterface $tokenStorage
     * @param ConfigManager $configManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ReverseSyncProcessor $reverseSyncProcessor,
        JobRunner $jobRunner,
        TokenStorageInterface $tokenStorage,
        ConfigManager $configManager,
        LoggerInterface $logger
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->reverseSyncProcessor = $reverseSyncProcessor;
        $this->jobRunner = $jobRunner;
        $this->tokenStorage = $tokenStorage;
        $this->configManager = $configManager;
        $this->logger = $logger;
        $strategyLogger = $this->reverseSyncProcessor->getLoggerStrategy();
        if ($strategyLogger instanceof LoggerStrategy) {
            $strategyLogger->setLogger($logger);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::WEBHOOK_NOTIFY];
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $maxRedeliverCount = (int)$this->configManager->get('marello_webhook.notification_redelivery');
        $redeliverCount = (int)$message->getProperty(RedeliveryMessageExtension::PROPERTY_REDELIVER_COUNT, '0');
        if($redeliverCount > $maxRedeliverCount) {
            $this->logger->critical('Re-deliver count exceeded.');
            return self::REJECT;
        }
        $messageBody = $message->getBody();

        /** @var EntityManagerInterface $em */
        $em = $this->doctrineHelper->getEntityManagerForClass(Integration::class);

        /** @var Integration $integration */
        $integration = $em->find(Integration::class, $messageBody['integration_id']);
        if (!$integration || !$integration->isEnabled()) {
            $this->logger->critical('Integration should exist and be enabled');

            return self::REJECT;
        }

        $result = $this->jobRunner->runUnique(
            $message->getMessageId(),
            Topics::WEBHOOK_NOTIFY . ':'. $messageBody['integration_id']. '_'. uniqid('', true),
            function () use ($integration, $messageBody) {
                $this->setTemporaryIntegrationToken($integration);
                return $this->reverseSyncProcessor->process(
                    $integration,
                    $messageBody['connector'] ?? WebhookNotificationConnector::TYPE,
                    $messageBody['connector_parameters']
                );
            }
        );

        return $result ? self::ACK : self::REJECT;
    }
}
