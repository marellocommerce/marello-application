<?php

namespace Marello\Bundle\NotificationMessageBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\NotificationMessageBundle\DependencyInjection\Configuration;
use Marello\Bundle\NotificationMessageBundle\Entity\NotificationMessage;
use Marello\Bundle\NotificationMessageBundle\Event\CreateNotificationMessageEvent;
use Marello\Bundle\NotificationMessageBundle\Event\ResolveNotificationMessageEvent;
use Marello\Bundle\NotificationMessageBundle\Model\NotificationMessageContext;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageSourceInterface;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageTypeInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\UserBundle\Entity\Group;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationMessageEventListener
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        private ManagerRegistry $registry,
        private ConfigManager $configManager,
        private TranslatorInterface $translator
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
        $existingMessage = $this->getEntityManager()
            ->getRepository(NotificationMessage::class)
            ->findOneBy($attributes);
        if ($existingMessage) {
            $existingMessage->increaseCount();
        } else {
            $notificationMessage = $this->createNewNotificationMessage($context, $attributes);
            $this->getEntityManager()->persist($notificationMessage);
        }

        if ($context->flush) {
            $this->getEntityManager()->flush();
        }
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

            if ($context->flush) {
                $this->getEntityManager()->flush();
            }
        }
    }

    /**
     * @param NotificationMessageContext $context
     * @param array $attributes
     * @return NotificationMessage
     */
    private function createNewNotificationMessage(
        NotificationMessageContext $context,
        array $attributes
    ): NotificationMessage {
        $notificationMessage = new NotificationMessage();
        $notificationMessage->setTitle($context->title);
        $notificationMessage->setMessage(
            $this->translator->trans($context->message, [], 'notificationMessage')
        );
        if ($context->solution) {
            $notificationMessage->setSolution(
                $this->translator->trans($context->solution, [], 'notificationMessage')
            );
        }
        $notificationMessage->setRelatedItemClass($attributes['relatedItemClass']);
        $notificationMessage->setRelatedItemId($attributes['relatedItemId']);
        $notificationMessage->setResolved($this->getEnumValue(
            NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_ENUM_CODE,
            $context->resolved
        ));
        $notificationMessage->setAlertType($this->getEnumValue(
            NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_ENUM_CODE,
            $context->alertType
        ));
        $notificationMessage->setSource($this->getEnumValue(
            NotificationMessageSourceInterface::NOTIFICATION_MESSAGE_SOURCE_ENUM_CODE,
            $context->source
        ));

        if ($context->operation) {
            $notificationMessage->setOperation($context->operation);
        }
        if ($context->step) {
            $notificationMessage->setStep($context->step);
        }
        if ($context->externalId) {
            $notificationMessage->setExternalId($context->externalId);
        }
        if ($context->log) {
            $notificationMessage->setLog($context->log);
        }
        if ($context->organization) {
            $notificationMessage->setOrganization($context->organization);
        }

        $groupConfiguration = $this->configManager->get(Configuration::SYSTEM_CONFIG_PATH_ASSIGNED_GROUPS);
        $configKey = sprintf('%s_%s', $context->source, $context->alertType);
        if (!empty($groupConfiguration[$configKey])) {
            $group = $this->getEntityManager()->getRepository(Group::class)->find($groupConfiguration[$configKey]);
            $notificationMessage->setUserGroup($group);
        }

        return $notificationMessage;
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
            'relatedItemClass' => null,
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
            $this->em = $this->registry->getManagerForClass(NotificationMessage::class);
        }

        return $this->em;
    }
}
