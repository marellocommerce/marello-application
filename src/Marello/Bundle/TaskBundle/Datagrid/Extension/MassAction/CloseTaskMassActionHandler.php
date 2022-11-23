<?php

namespace Marello\Bundle\TaskBundle\Datagrid\Extension\MassAction;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerArgs;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityMergeBundle\Datasource\Orm\MergeIterableResult;
use Symfony\Contracts\Translation\TranslatorInterface;

class CloseTaskMassActionHandler implements MassActionHandlerInterface
{
    const FLUSH_BATCH_SIZE = 100;

    public function __construct(
        protected ManagerRegistry $registry,
        protected TranslatorInterface $translator
    ) {
    }

    public function handle(MassActionHandlerArgs $args)
    {
        $options = $args->getMassAction()->getOptions();
        $entityName = $options->offsetGet('entity_name');
        $entityIdentifierField = $this->getEntityIdentifierField($options);

        $source = $args->getResults()->getSource();
        $result = new MergeIterableResult($source);
        $result->setBufferSize(self::FLUSH_BATCH_SIZE);

        $statusClass = ExtendHelper::buildEnumValueClassName('task_status');
        $statusClosed = $this->registry->getManagerForClass($statusClass)->find($statusClass, 'closed');

        $manager = $this->registry->getManagerForClass($entityName);
        $totalCount = 0;
        foreach ($result as $record) {
            $entity = $record->getRootEntity();
            $identifierValue = $record->getValue($entityIdentifierField);
            if (!$entity) {
                $entity = $manager->getReference($entityName, $identifierValue);
            }

            $entity->setStatus($statusClosed);
            $totalCount++;
        }

        $manager->flush();

        $responseMessage = $options->offsetGetByPath('[messages][success]');
        $options = ['count' => $totalCount];

        return new MassActionResponse(
            true,
            $this->translator->trans(
                $responseMessage,
                ['%count%' => $totalCount]
            ),
            $options
        );
    }

    protected function getEntityIdentifierField(ActionConfiguration $options): string
    {
        $identifier = $options->offsetGet('data_identifier');

        // if we ask identifier that's means that we have plain data in array
        // so we will just use column name without entity alias
        if (strpos('.', $identifier) !== -1) {
            $parts = explode('.', $identifier);
            $identifier = end($parts);
        }

        return $identifier;
    }
}
