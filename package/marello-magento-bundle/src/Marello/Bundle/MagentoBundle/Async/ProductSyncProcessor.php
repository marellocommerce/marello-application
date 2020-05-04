<?php
namespace Marello\Bundle\MagentoBundle\Async;

use Marello\Bundle\MagentoBundle\Provider\Connector\ProductConnector;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Psr\Log\LoggerInterface;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Oro\Bundle\IntegrationBundle\Authentication\Token\IntegrationTokenAwareTrait;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository as IntegrationRepository;
use Oro\Bundle\MagentoBundle\Exception\ExtensionRequiredException;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;

class ProductSyncProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    use IntegrationTokenAwareTrait;

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * @var JobRunner
     */
    private $jobRunner;

    /**
     * @var JobExecutor
     */
    private $jobExecutor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RegistryInterface $doctrine
     * @param JobRunner $jobRunner
     * @param TokenStorageInterface $tokenStorage
     * @param LoggerInterface $logger
     */
    public function __construct(
        RegistryInterface $doctrine,
        JobRunner $jobRunner,
        JobExecutor $jobExecutor,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $logger
    ) {
        $this->doctrine = $doctrine;
        $this->jobRunner = $jobRunner;
        $this->jobExecutor = $jobExecutor;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::SYNC_PRODUCT_ENTITY_INTEGRATION];
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $body = JSON::decode($message->getBody());
        $body = array_replace_recursive([
            'integration_id' => null,
        ], $body);

        if (! $body['integration_id']) {
            $this->logger->critical('The message invalid. It must have integrationId set');

            return self::REJECT;
        }

        /** @var IntegrationRepository $repository */
        $repository = $this->doctrine->getRepository(Integration::class);
        $integration = $repository->getOrLoadById($body['integration_id']);

        if (! $integration || ! $integration->isEnabled()) {
            $this->logger->error(
                sprintf('The integration should exist and be enabled: %s', $body['integrationId'])
            );

            return self::REJECT;
        }

        if (! is_array($integration->getConnectors())
            || ! in_array(ProductConnector::TYPE, $integration->getConnectors())) {
            $this->logger->error(
                sprintf('The integration should have product in connectors: %s', $body['integration_id']),
                ['integration' => $integration]
            );

            return self::REJECT;
        }

        try {
            $ownerId = $message->getMessageId();
            $jobName = Topics::SYNC_PRODUCT_ENTITY_INTEGRATION .':'.$body['integration_id'].':'.$body['id'];

            $result = $this->jobRunner->runUnique($ownerId, $jobName, function () use ($integration, $body) {
                $this->setTemporaryIntegrationToken($integration);

                switch ($body["connector_parameters"]['action']) {
                    case Topics::SYNC_REMOVE_ACTION:
                        $integrationJob = ProductConnector::DELETE_JOB_NAME;
                        $integrationProcessorAlias = ProductConnector::DELETE_PROCESSOR_ALIAS;
                        break;
                    default:
                        $integrationJob = ProductConnector::EXPORT_JOB_NAME;
                        $integrationProcessorAlias = ProductConnector::EXPORT_PROCESSOR_ALIAS;
                        break;
                }

                $jobResult = $this->jobExecutor->executeJob(
                    ProcessorRegistry::TYPE_EXPORT,
                    $integrationJob,
                    array_merge(
                        [
                            'channel' => $integration,
                            'writer_skip_clear' => true,
                            'processorAlias' => $integrationProcessorAlias,
                        ],
                        $body["connector_parameters"]
                    )
                );

                if (!$jobResult->isSuccessful()) {
                    throw new \Exception(implode(" ", $jobResult->getFailureExceptions()));
                }

                return $jobResult->isSuccessful();
            });
        } catch (ExtensionRequiredException $e) {
            $this->logger->warning($e->getMessage(), ['exception' => $e]);

            return self::REJECT;
        }

        return $result ? self::ACK : self::REJECT;
    }
}
