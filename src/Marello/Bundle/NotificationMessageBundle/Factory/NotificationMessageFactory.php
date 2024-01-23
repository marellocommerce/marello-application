<?php

namespace Marello\Bundle\NotificationMessageBundle\Factory;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Contracts\Translation\TranslatorInterface;

use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;

use Marello\Bundle\NotificationMessageBundle\Entity\NotificationMessage;
use Marello\Bundle\NotificationMessageBundle\Model\NotificationMessageContext;
use Marello\Bundle\NotificationMessageBundle\DependencyInjection\Configuration;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageTypeInterface;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageSourceInterface;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface;

class NotificationMessageFactory
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        private DoctrineHelper $doctrineHelper,
        private ConfigManager $configManager,
        private TranslatorInterface $translator
    ) {
    }

    /**
     * @param array $attributes
     * @param NotificationMessageContext $context
     * @return NotificationMessage
     */
    public function createNewNotificationMessage(
        array $attributes,
        NotificationMessageContext $context = null
    ): NotificationMessage {
        $notificationMessage = new NotificationMessage();
        $notificationMessage->setTitle($context->title ?? $attributes['title']);
        $notificationMessage->setMessage(
            $this->translator->trans($context->message ?? $attributes['message'], [], 'notificationMessage')
        );
        if ($context->solution || isset($attributes['solution'])) {
            $notificationMessage->setSolution(
                $this->translator->trans($context->solution ?? $attributes['solution'], [], 'notificationMessage')
            );
        }
        $notificationMessage->setRelatedItemClass($attributes['relatedItemClass']);
        $notificationMessage->setRelatedItemId($attributes['relatedItemId']);
        $notificationMessage->setResolved($this->getEnumValue(
            NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_ENUM_CODE,
            $context->resolved ?? $attributes['resolved']
        ));
        $notificationMessage->setAlertType($this->getEnumValue(
            NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_ENUM_CODE,
            $context->alertType ?? $attributes['alertType']
        ));
        $notificationMessage->setSource($this->getEnumValue(
            NotificationMessageSourceInterface::NOTIFICATION_MESSAGE_SOURCE_ENUM_CODE,
            $context->source ?? $attributes['source']
        ));

        if ($context->operation || isset($attributes['operation'])) {
            $notificationMessage->setOperation($context->operation ?? $attributes['operation']);
        }
        if ($context->step || isset($attributes['step'])) {
            $notificationMessage->setStep($context->step ?? $attributes['step']);
        }
        if ($context->externalId || isset($attributes['externalId'])) {
            $notificationMessage->setExternalId($context->externalId ?? $attributes['step']);
        }
        if ($context->log || isset($attributes['log'])) {
            $notificationMessage->setLog($context->log ?? $attributes['log']);
        }
        if ($context->organization || isset($attributes['organization'])) {
            $organization = $context->organization ?? $attributes['organization'];
            if (is_int($organization)) {
                $organization = $this
                    ->getEntityManager()
                    ->getRepository(Organization::class)
                    ->find($organization);
            }
            $notificationMessage->setOrganization($organization);
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
    private function getEntityManager(): ?EntityManagerInterface
    {
        if (!$this->em) {
            $this->em = $this->doctrineHelper->getEntityManagerForClass(NotificationMessage::class);
        }

        return $this->em;
    }
}
