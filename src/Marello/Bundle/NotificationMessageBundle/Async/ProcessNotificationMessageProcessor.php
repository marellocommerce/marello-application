<?php

namespace Marello\Bundle\NotificationMessageBundle\Async;

use Psr\Log\LoggerInterface;

use Oro\Component\MessageQueue\Util\JSON;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;

use Marello\Bundle\NotificationMessageBundle\Entity\NotificationMessage;
use Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageFactory;
use Marello\Bundle\NotificationMessageBundle\Async\Topic\ProcessNotificationMessageTopic;

class ProcessNotificationMessageProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @param LoggerInterface $logger
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        private LoggerInterface $logger,
        private DoctrineHelper $doctrineHelper,
        private NotificationMessageFactory $messageFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [ProcessNotificationMessageTopic::getName()];
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $data = JSON::decode($message->getBody());
        if (!isset($data['title']) ||
            !isset($data['message']) ||
            !isset($data['entity_class'])
        ) {
            $this->logger->critical(
                sprintf('Got invalid message. "%s"', $message->getBody()),
                ['message' => $message]
            );

            return self::REJECT;
        }

        try {
            $baseAttributes =  [
                'alertType' => $data['alertType'],
                'source' => $data['source'],
                'title' => $data['title'],
                'resolved' => $data['resolved'],
                'relatedItemId' => $data['relatedItemId'],
                'relatedItemClass' => $data['relatedItemClass']
            ];
            /** @var NotificationMessage $existingMessage */
            $existingMessage = $this->doctrineHelper
                ->getEntityRepositoryForClass(NotificationMessage::class)
                ->findOneBy($baseAttributes);
            if ($existingMessage) {
                $existingMessage->increaseCount();
            } else {
                $notificationMessage = $this->messageFactory->createNewNotificationMessage($data);
                $this->doctrineHelper
                    ->getEntityManagerForClass(NotificationMessage::class)
                    ->persist($notificationMessage);
            }
            $this->doctrineHelper->getEntityManagerForClass(NotificationMessage::class)->flush();
        } catch (\InvalidArgumentException $e) {
            $this->logger->error(
                sprintf(
                    'Message is invalid: %s. Original message: "%s"',
                    $e->getMessage(),
                    $message->getBody()
                )
            );

            return self::REJECT;
        } catch (\Exception $e) {
            $this->logger->error(
                'Unexpected exception occurred during transit',
                ['exception' => $e]
            );

            return self::REJECT;
        }

        return self::ACK;
    }
}
