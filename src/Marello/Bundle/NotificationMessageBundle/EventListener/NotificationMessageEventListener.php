<?php

namespace Marello\Bundle\NotificationMessageBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Contracts\Translation\TranslatorInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

use Marello\Bundle\NotificationMessageBundle\Entity\NotificationMessage;
use Marello\Bundle\NotificationMessageBundle\Model\NotificationMessageContext;
use Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageFactory;
use Marello\Bundle\NotificationMessageBundle\Event\CreateNotificationMessageEvent;
use Marello\Bundle\NotificationMessageBundle\Event\ResolveNotificationMessageEvent;
use Marello\Bundle\NotificationMessageBundle\Async\Topic\ProcessNotificationMessageTopic;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface;

class NotificationMessageEventListener
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        private DoctrineHelper $doctrineHelper,
        private TranslatorInterface $translator,
        private MessageProducerInterface $messageProducer,
        private NotificationMessageFactory $messageFactory
    ) {
    }

    /**
     * @param CreateNotificationMessageEvent $event
     * @return void
     */
    public function onCreate(CreateNotificationMessageEvent $event): void
    {
        $context = $event->getContext();

        $attributes = $this->getBaseAttributes($context);
        if ($context->relatedEntity) {
            $attributes['relatedItemClass'] = get_class($context->relatedEntity);
            $idValues = $this->getEntityManager()
                ->getClassMetadata($attributes['relatedItemClass'])
                ->getIdentifierValues($context->relatedEntity);
            $attributes['relatedItemId'] = reset($idValues);
        }

        /** @var NotificationMessage $existingMessage */
        $notificationMessage = $this->getEntityManager()
            ->getRepository(NotificationMessage::class)
            ->findOneBy($attributes);
        if ($notificationMessage) {
            $notificationMessage->increaseCount();
        } else {
            $notificationMessage = $this->messageFactory->createNewNotificationMessage($attributes, $context);
            $this->getEntityManager()->persist($notificationMessage);
        }

        $this->processMessage($context, $notificationMessage);
    }

    /**
     * @param ResolveNotificationMessageEvent $event
     * @return void
     */
    public function onResolve(ResolveNotificationMessageEvent $event): void
    {
        $context = $event->getContext();

        $attributes = $this->getBaseAttributes($context);
        if ($context->relatedEntity) {
            $attributes['relatedItemClass'] = get_class($context->relatedEntity);
            $idValues = $this->getEntityManager()
                ->getClassMetadata($attributes['relatedItemClass'])
                ->getIdentifierValues($context->relatedEntity);
            $attributes['relatedItemId'] = reset($idValues);
        }

        /** @var NotificationMessage $existingMessage */
        $existingMessage = $this->getEntityManager()
            ->getRepository(NotificationMessage::class)
            ->findOneBy($attributes);
        if ($existingMessage) {
            $resolved = $this->getEnumValue(
                NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_ENUM_CODE,
                NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_YES
            );
            $existingMessage->setResolved($resolved);
            $this->processMessage($context, $existingMessage);
        }
    }

    /**
     * @param NotificationMessageContext $context
     * @return array
     */
    private function getBaseAttributes(NotificationMessageContext $context): array
    {
        $context->title = $this->translator->trans($context->title, [], 'notificationMessage');

        return [
            'alertType' => $context->alertType,
            'source' => $context->source,
            'title' => $context->title,
            'resolved' => $context->resolved,
            'relatedItemId' => null,
            'relatedItemClass' => null
        ];
    }

    /**
     * @param string $code
     * @param string $id
     * @return AbstractEnumValue
     */
    private function getEnumValue(string $code, string $id): AbstractEnumValue
    {
        $enumRepo = $this->getEntityManager()->getRepository(ExtendHelper::buildEnumValueClassName($code));
        $enumValue = $enumRepo->find($id);
        if (!$enumValue) {
            throw new \LogicException(sprintf('Wrong enum id "%s" for "%s" enum code', $id, $code));
        }

        return $enumValue;
    }

    /**
     * @return EntityManagerInterface
     */
    private function getEntityManager(): EntityManagerInterface
    {
        if (!$this->em) {
            $this->em = $this->doctrineHelper->getEntityManagerForClass(NotificationMessage::class);
        }

        return $this->em;
    }

    private function processMessage(NotificationMessageContext $context, NotificationMessage $message): void
    {
        if ($context->flush && !$context->queue) {
            $this->getEntityManager()->flush();
        }

        if ($context->queue) {
            $this->messageProducer->send(
                ProcessNotificationMessageTopic::getName(),
                [
                    'title' => $context->title,
                    'message' => $context->message,
                    'solution' => $context->solution,
                    'relatedItemClass' => $message->getRelatedItemClass(),
                    'relatedItemId' => $message->getRelatedItemId(),
                    'resolved' => $context->resolved,
                    'alertType' => $context->alertType,
                    'source' => $context->source,
                    'operation' => $context->operation,
                    'step' => $context->step,
                    'externalId' => $context->externalId,
                    'log' => $context->log,
                    'organization' => $context->organization->getId(),
                    'entity_class' => NotificationMessage::class,
                    'priority' => MessagePriority::LOW
                ]
            );
        }
    }
}
