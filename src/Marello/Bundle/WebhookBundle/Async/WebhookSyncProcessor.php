<?php

namespace Marello\Bundle\WebhookBundle\Async;

use Marello\Bundle\WebhookBundle\Async\Topic\WebhookSyncTopic;
use Marello\Bundle\WebhookBundle\Integration\Connector\WebhookNotificationConnector;
use Marello\Bundle\WebhookBundle\Manager\WebhookProvider;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\IntegrationBundle\Authentication\Token\IntegrationTokenAwareTrait;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ReverseSyncProcessor;
use Oro\Bundle\MessageQueueBundle\Consumption\Extension\RedeliveryMessageExtension;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WebhookSyncProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface
{
    use IntegrationTokenAwareTrait;

    public function __construct(
        private WebhookProvider $webhookProvider,
        private ReverseSyncProcessor $reverseSyncProcessor,
        private JobRunner $jobRunner,
        TokenStorageInterface $tokenStorage,
        private ConfigManager $configManager,
        private LoggerInterface $logger
    ) {
        $this->tokenStorage = $tokenStorage;
        $strategyLogger = $this->reverseSyncProcessor->getLoggerStrategy();
        if ($strategyLogger instanceof LoggerStrategy) {
            $strategyLogger->setLogger($logger);
        }
    }

    public static function getSubscribedTopics()
    {
        return [WebhookSyncTopic::getName()];
    }

    public function process(MessageInterface $message, SessionInterface $session)
    {
        $maxRedeliverCount = (int)$this->configManager->get('marello_webhook.notification_redelivery');
        $redeliverCount = (int)$message->getProperty(RedeliveryMessageExtension::PROPERTY_REDELIVER_COUNT, '0');
        if ($redeliverCount > $maxRedeliverCount) {
            $this->logger->critical('Re-deliver count exceeded.');

            return self::REJECT;
        }

        $messageBody = $message->getBody();
        $integration = $this->webhookProvider->getWebhookIntergrationById($messageBody['integration_id']);
        if (!$integration || !$integration->isEnabled()) {
            $this->logger->critical('Integration should exist and be enabled');

            return self::REJECT;
        }

        $result = $this->jobRunner->runUniqueByMessage(
            $message,
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
