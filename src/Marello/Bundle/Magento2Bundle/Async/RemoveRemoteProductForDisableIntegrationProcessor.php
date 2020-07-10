<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Doctrine\DBAL\Exception\RetryableException;
use Doctrine\ORM\NonUniqueResultException;
use Marello\Bundle\Magento2Bundle\Entity\Product;
use Marello\Bundle\Magento2Bundle\Entity\Repository\ProductRepository;
use Marello\Bundle\Magento2Bundle\Transport\RestTransport;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class RemoveRemoteProductForDisableIntegrationProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var JobRunner */
    protected $jobRunner;

    /** @var RestTransport */
    protected $transport;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /**
     * @param JobRunner $jobRunner
     * @param RestTransport $transport
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(
        JobRunner $jobRunner,
        RestTransport $transport,
        ManagerRegistry $managerRegistry
    ) {
        $this->jobRunner = $jobRunner;
        $this->transport = $transport;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $context = [];
        try {
            $wrappedMessage = RemoveRemoteProductForDisableIntegrationMessage::createFromMessage(
                $message
            );
            $context = $wrappedMessage->getContextParams();

            $result = $this
                ->jobRunner
                ->runDelayed($wrappedMessage->getJobId(), function () use ($wrappedMessage) {
                    $this->processRemovingProduct($wrappedMessage);

                    return true;
                });
        } catch (\Throwable $exception) {
            $context['exception'] = $exception;

            $this->logger->critical(
                '[Magento 2] Remove Remote Product for disabled integration failed. Reason: ' .
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
     * @param RemoveRemoteProductForDisableIntegrationMessage $message
     * @throws NonUniqueResultException
     * @throws RestException
     */
    protected function processRemovingProduct(RemoveRemoteProductForDisableIntegrationMessage $message): void
    {
        $this->transport->initWithSettingBag($message->getTransportSettingBag());
        $isSuccessfull = $this->transport->removeProduct($message->getProductSku());
        if ($isSuccessfull && $message->isDeactivated()) {
            $productManager = $this->managerRegistry->getManagerForClass(Product::class);
            /** @var ProductRepository $productRepository */
            $productRepository = $productManager->getRepository(Product::class);
            $product = $productRepository->getMagentoProductByChannelIdAndProductId(
                $message->getIntegrationId(),
                $message->getProductId()
            );

            if ($product === null) {
                return;
            }

            $productManager->remove($product);
            $productManager->flush();
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::REMOVE_REMOTE_PRODUCT_FOR_DISABLED_INTEGRATION];
    }
}
