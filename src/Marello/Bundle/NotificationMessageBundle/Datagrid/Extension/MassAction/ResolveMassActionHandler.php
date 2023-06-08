<?php

namespace Marello\Bundle\NotificationMessageBundle\Datagrid\Extension\MassAction;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\NotificationMessageBundle\Entity\NotificationMessage;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerArgs;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResolveMassActionHandler implements MassActionHandlerInterface
{
    public function __construct(
        private ManagerRegistry $registry,
        private TranslatorInterface $translator
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(MassActionHandlerArgs $args)
    {
        $em = $this->registry->getManagerForClass(NotificationMessage::class);
        $options = $args->getMassAction()->getOptions();
        $entityIdentifierField = $this->getEntityIdentifierField($options);
        $entityName = $options->offsetGet('entity_name');

        $className = ExtendHelper::buildEnumValueClassName(
            NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_ENUM_CODE
        );
        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $em->getRepository($className);
        $resolvedYes = $enumRepo->find(NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_YES);

        $result = $args->getResults();
        $counter = 0;
        foreach ($result as $record) {
            $notificationMessage = $record->getRootEntity();
            if (!$notificationMessage) {
                $identifierValue = $record->getValue($entityIdentifierField);
                $notificationMessage = $em->getReference($entityName, $identifierValue);
            }
            /** @var NotificationMessage $notificationMessage */
            if ($notificationMessage->getResolved()->getId()
                === NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_NO
            ) {
                $notificationMessage->setResolved($resolvedYes);
                $counter++;
            }
        }

        if ($counter === 0) {
            return new MassActionResponse(
                true,
                $this->translator->trans('marello.notificationmessage.mass_actions.resolve.no_items')
            );
        }

        $em->flush();

        return new MassActionResponse(
            true,
            $this->translator->trans(
                'marello.notificationmessage.mass_actions.resolve.success',
                [
                    '%total%' => $counter,
                ]
            )
        );
    }

    protected function getEntityIdentifierField(ActionConfiguration $options): string
    {
        $identifier = $options->offsetGet('data_identifier');

        if (strpos('.', $identifier) !== -1) {
            $parts = explode('.', $identifier);
            $identifier = end($parts);
        }

        return $identifier;
    }
}
