<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Doctrine\DBAL\Exception\RetryableException;
use Marello\Bundle\Magento2Bundle\Entity\Product;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class ClearInternalDataForDisabledIntegrationProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var JobRunner */
    protected $jobRunner;

    /** @var MessageProducerInterface */
    protected $producer;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /**
     * @param JobRunner $jobRunner
     * @param MessageProducerInterface $producer
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(
        JobRunner $jobRunner,
        MessageProducerInterface $producer,
        ManagerRegistry $managerRegistry
    ) {
        $this->jobRunner = $jobRunner;
        $this->producer = $producer;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $context = [];
        try {
            $wrappedMessage = ClearInternalDataForDisabledIntegrationMessage::createFromMessage(
                $message
            );
            $context = $wrappedMessage->getContextParams();

            if (!$this->isIntegrationApplicable($wrappedMessage)) {
                $this->logger->debug(
                    '[Magento 2] Clear internal data for disabled integration is not allow to start.',
                    $context
                );

                return self::REJECT;
            }

            $jobName = sprintf(
                '%s:%s',
                'marello_magento2:clear_internal_data_for_disabled_integration',
                $wrappedMessage->getIntegrationId()
            );

            $result = $this->jobRunner->runUnique(
                $message->getMessageId(),
                $jobName,
                function (JobRunner $jobRunner) use ($wrappedMessage) {
                    $this->removeInternalProducts($wrappedMessage->getIntegrationId());
                    $this->unsetLinksBetweenSalesChannelsAndWebsites($wrappedMessage->getIntegrationId());

                    return true;
                }
            );

        } catch (\Throwable $exception) {
            $context['exception'] = $exception;

            $this->logger->critical(
                '[Magento 2] Clear internal data for disabled integration failed. Reason: ' .
                $exception->getMessage(),
                $context
            );

            if ($exception instanceof RetryableException) {
                return self::REQUEUE;
            }

            return self::REJECT;
        }

        /**
         * Reject in case when same unique job already running
         */
        return $result ? self::ACK : self::REJECT;
    }

    /**
     * @param int $integrationId
     */
    protected function unsetLinksBetweenSalesChannelsAndWebsites(int $integrationId): void
    {
        $em = $this->managerRegistry->getManagerForClass(Website::class);

        /** @var Website[] $websites */
        $websites = $em
            ->getRepository(Website::class)
            ->findBy(['channel' => $integrationId]);

        if (empty($websites)) {
            return;
        }

        foreach ($websites as $website) {
            $website->setSalesChannel(null);
        }

        $em->flush();
    }

    /**
     * @param int $integrationId
     */
    protected function removeInternalProducts(int $integrationId): void
    {
        $this
            ->managerRegistry
            ->getRepository(Product::class)
            ->deleteByIntegrationId($integrationId);
    }

    /**
     * @param IntegrationAwareMessageInterface $message
     * @return bool
     */
    protected function isIntegrationApplicable(IntegrationAwareMessageInterface $message): bool
    {
        /** @var Integration $integration */
        $integration = $this->managerRegistry
            ->getRepository(Integration::class)
            ->find($message->getIntegrationId());

        return $integration && !$integration->isEnabled();
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::CLEAR_INTERNAL_DATA_FOR_DISABLED_INTEGRATION];
    }
}
