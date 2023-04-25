<?php
namespace Marello\Bundle\WebhookBundle\Async;

use Marello\Bundle\WebhookBundle\Integration\Connector\WebhookNotificationConnector;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Authentication\Token\IntegrationTokenAwareTrait;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\ReverseSyncProcessor;
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

    /**
     * @var DoctrineHelper
     */
    private DoctrineHelper $doctrineHelper;

    /**
     * @var ReverseSyncProcessor
     */
    private ReverseSyncProcessor $reverseSyncProcessor;

    /**
     * @var JobRunner
     */
    private JobRunner $jobRunner;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(
        DoctrineHelper $doctrineHelper,
        ReverseSyncProcessor $reverseSyncProcessor,
        JobRunner $jobRunner,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $logger
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->reverseSyncProcessor = $reverseSyncProcessor;
        $this->jobRunner = $jobRunner;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
        $this->reverseSyncProcessor->getLoggerStrategy()->setLogger($logger);
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
