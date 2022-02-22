<?php

namespace Marello\Bundle\WorkflowBundle\Datagrid\Extension\MassAction;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionExtension;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionFactory;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionMetadataFactory;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class WorkflowTransitMassActionExtension extends MassActionExtension
{
    public const MASS_ACTION_TYPE = 'workflowtransit';

    /**
     * @var WorkflowRegistry
     */
    protected $workflowRegistry;

    /**
     * @var EntityClassResolver
     */
    protected $entityClassResolver;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        MassActionFactory $actionFactory,
        MassActionMetadataFactory $actionMetadataFactory,
        AuthorizationCheckerInterface $authorizationChecker,
        WorkflowRegistry $workflowRegistry,
        EntityClassResolver $entityClassResolver,
        LoggerInterface $logger
    ) {
        parent::__construct($actionFactory, $actionMetadataFactory, $authorizationChecker);
        $this->workflowRegistry = $workflowRegistry;
        $this->entityClassResolver = $entityClassResolver;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function createAction($actionName, array $actionConfig)
    {
        $action = parent::createAction($actionName, $actionConfig);
        if ($actionConfig['type'] !== self::MASS_ACTION_TYPE) {
            return $action;
        }

        $workflow = $this->workflowRegistry->getWorkflow($actionConfig['workflow'], false);
        if (!$workflow) {
            $this->logger->warning(sprintf(
                'Mass action "%s": Workflow %s does not exist.',
                self::MASS_ACTION_TYPE,
                $actionConfig['workflow']
            ));

            return null;
        }

        $transition = $workflow->getTransitionManager()->getTransition($actionConfig['transition']);
        if (!$transition) {
            $this->logger->warning(sprintf(
                'Mass action "%s": Transition %s does not exist.',
                self::MASS_ACTION_TYPE,
                $actionConfig['transition']
            ));

            return null;
        }

        return $action;
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionsMetadata(DatagridConfiguration $config)
    {
        $actionsMetadata = parent::getActionsMetadata($config);
        foreach ($actionsMetadata as $actionName => $actionMetadata) {
            if ($actionMetadata['type'] !== self::MASS_ACTION_TYPE) {
                continue;
            }

            $result = $this->assertEntityName($config, $actionMetadata);
            if (!$result) {
                $this->logger->warning(sprintf(
                    'Mass action "%s": Wrong entity name %s.',
                    self::MASS_ACTION_TYPE,
                    $actionMetadata['entity_name']
                ));

                unset($actionsMetadata[$actionName]);
            }
        }

        return $actionsMetadata;
    }

    protected function assertEntityName(DatagridConfiguration $config, iterable $actionMetadata): bool
    {
        if ($config->offsetExists('extended_entity_name')) {
            $extendedEntityName = $this->entityClassResolver->getEntityClass(
                $config->offsetGet('extended_entity_name')
            );

            return $extendedEntityName === $actionMetadata['entity_name'];
        }

        $source = $config->offsetGet('source');
        if ($source['type'] !== OrmDatasource::TYPE) {
            return true;
        }

        $from = reset($source['query']['from']);

        return $this->entityClassResolver->getEntityClass($from['table']) === $actionMetadata['entity_name'];
    }
}
