<?php

namespace Marello\Bundle\NotificationAlertBundle\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\NotificationAlertBundle\Entity\NotificationAlert;
use Marello\Bundle\NotificationAlertBundle\Event\CreateNotificationAlertEvent;
use Marello\Bundle\NotificationAlertBundle\Model\NotificationAlertContext;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertResolvedInterface;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertSourceInterface;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertTypeInterface;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateNotificationAlertEventListener
{
    public function __construct(
        private ManagerRegistry $registry,
        private TranslatorInterface $translator
    ) {}

    public function onCreate(CreateNotificationAlertEvent $event): void
    {
        $em = $this->registry->getManagerForClass(NotificationAlert::class);
        $context = $event->getContext();

        $attributes = $this->getBaseAttributes($context);
        if ($context->relatedEntity) {
            $attributes['relatedItemClass'] = get_class($context->relatedEntity);
            $idValues = $em->getClassMetadata($attributes['relatedItemClass'])
                ->getIdentifierValues($context->relatedEntity);
            $attributes['relatedItemId'] = reset($idValues);
        }

        /** @var NotificationAlert $existingAlert */
        $existingAlert = $em->getRepository(NotificationAlert::class)->findOneBy($attributes);
        if ($existingAlert) {
            $existingAlert->increaseCount();
        } else {
            $notificationAlert = new NotificationAlert();
            $notificationAlert->setMessage($context->message);
            if ($context->solution) {
                $notificationAlert->setSolution(
                    $this->translator->trans($context->solution, [], 'notificationAlert')
                );
            }
            $notificationAlert->setRelatedItemClass($attributes['relatedItemClass']);
            $notificationAlert->setRelatedItemId($attributes['relatedItemId']);
            $notificationAlert->setResolved($this->getEnumValue(
                $em,
                NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_ENUM_CODE,
                $context->resolved
            ));
            $notificationAlert->setAlertType($this->getEnumValue(
                $em,
                NotificationAlertTypeInterface::NOTIFICATION_ALERT_TYPE_ENUM_CODE,
                $context->alertType
            ));
            $notificationAlert->setSource($this->getEnumValue(
                $em,
                NotificationAlertSourceInterface::NOTIFICATION_ALERT_SOURCE_ENUM_CODE,
                $context->source
            ));
            if ($attributes['relatedItemClass']
                && $notificationAlert->supportActivityTarget($attributes['relatedItemClass'])
            ) {
                $notificationAlert->addActivityTarget($context->relatedEntity);
            }

            $em->persist($notificationAlert);
        }

        $em->flush();
    }

    private function getBaseAttributes(NotificationAlertContext $context): array
    {
        $context->message = $this->translator->trans($context->message, [], 'notificationAlert');

        return [
            'alertType' => $context->alertType,
            'source' => $context->source,
            'message' => $context->message,
            'resolved' => $context->resolved,
            'relatedItemId' => null,
            'relatedItemClass' => null,
        ];
    }

    private function getEnumValue(ObjectManager $em, string $code, string $id): AbstractEnumValue
    {
        $enumRepo = $em->getRepository(ExtendHelper::buildEnumValueClassName($code));
        $enumValue = $enumRepo->find($id);
        if (!$enumValue) {
            throw new \LogicException(sprintf('Wrong enum id "%s" for "%s" enum code', $id, $code));
        }

        return $enumValue;
    }
}
