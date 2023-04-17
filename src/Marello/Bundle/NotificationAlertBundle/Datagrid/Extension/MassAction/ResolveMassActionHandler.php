<?php

namespace Marello\Bundle\NotificationAlertBundle\Datagrid\Extension\MassAction;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\NotificationAlertBundle\Entity\NotificationAlert;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertResolvedInterface;
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
    ) {}

    /**
     * {@inheritdoc}
     */
    public function handle(MassActionHandlerArgs $args)
    {
        $em = $this->registry->getManagerForClass(NotificationAlert::class);
        $options = $args->getMassAction()->getOptions();
        $entityIdentifierField = $this->getEntityIdentifierField($options);
        $entityName = $options->offsetGet('entity_name');

        $className = ExtendHelper::buildEnumValueClassName(
            NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_ENUM_CODE
        );
        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $em->getRepository($className);
        $resolvedYes = $enumRepo->find(NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_YES);

        $result = $args->getResults();
        $counter = 0;
        foreach ($result as $record) {
            $notificationAlert = $record->getRootEntity();
            if (!$notificationAlert) {
                $identifierValue = $record->getValue($entityIdentifierField);
                $notificationAlert = $em->getReference($entityName, $identifierValue);
            }
            /** @var NotificationAlert $notificationAlert */
            if ($notificationAlert->getResolved()->getId()
                === NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_NO
            ) {
                $notificationAlert->setResolved($resolvedYes);
                $counter++;
            }
        }

        if ($counter === 0) {
            return new MassActionResponse(
                true,
                $this->translator->trans('marello.notificationalert.mass_actions.resolve.no_items')
            );
        }

        $em->flush();

        return new MassActionResponse(
            true,
            $this->translator->trans(
                'marello.notificationalert.mass_actions.resolve.success',
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
