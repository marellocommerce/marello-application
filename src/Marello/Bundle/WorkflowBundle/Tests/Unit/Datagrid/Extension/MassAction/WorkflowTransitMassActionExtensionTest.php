<?php

namespace Marello\Bundle\WorkflowBundle\Tests\Unit\Async;

use Marello\Bundle\WorkflowBundle\Datagrid\Extension\MassAction\WorkflowTransitMassAction;
use Marello\Bundle\WorkflowBundle\Datagrid\Extension\MassAction\WorkflowTransitMassActionExtension;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Action\Actions\ActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax\AjaxMassAction;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionExtension;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionFactory;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionMetadataFactory;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver;
use Oro\Bundle\WorkflowBundle\Model\Transition;
use Oro\Bundle\WorkflowBundle\Model\TransitionManager;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;
use Oro\Component\Config\Common\ConfigObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class WorkflowTransitMassActionExtensionTest extends TestCase
{
    /**
     * @var MassActionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $actionFactory;

    /**
     * @var MassActionMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $actionMetadataFactory;

    /**
     * @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $authorizationChecker;

    /**
     * @var WorkflowRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $workflowRegistry;

    /**
     * @var EntityClassResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    private $entityClassResolver;

    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var WorkflowTransitMassActionExtension
     */
    private $extension;

    protected function setUp(): void
    {
        $this->actionFactory = $this->createMock(MassActionFactory::class);
        $this->actionMetadataFactory = $this->createMock(MassActionMetadataFactory::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->workflowRegistry = $this->createMock(WorkflowRegistry::class);
        $this->entityClassResolver = $this->createMock(EntityClassResolver::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->extension = new WorkflowTransitMassActionExtension(
            $this->actionFactory,
            $this->actionMetadataFactory,
            $this->authorizationChecker,
            $this->workflowRegistry,
            $this->entityClassResolver,
            $this->logger
        );
    }

    public function testVisitMetadata()
    {
        $configArray = [
            'extended_entity_name' => \stdClass::class,
            'mass_actions' => [
                'other-action' => [
                    ConfigObject::NAME_KEY => 'other-action',
                    'type' => 'other',
                    'handler' => 'other-handler',
                ],
                'no-workflow' => [
                    ConfigObject::NAME_KEY => 'no-workflow',
                    'type' => WorkflowTransitMassActionExtension::MASS_ACTION_TYPE,
                    'workflow' => 'not-existing-workflow',
                    'transition' => 'not-existing-transition',
                    'entity_name' => \DateTime::class,
                ],
                'no-transition' => [
                    ConfigObject::NAME_KEY => 'no-transition',
                    'type' => WorkflowTransitMassActionExtension::MASS_ACTION_TYPE,
                    'workflow' => 'existing-workflow',
                    'transition' => 'not-existing-transition',
                    'entity_name' => \DateTime::class,
                ],
                'wrong-entity' => [
                    ConfigObject::NAME_KEY => 'wrong-entity',
                    'type' => WorkflowTransitMassActionExtension::MASS_ACTION_TYPE,
                    'workflow' => 'existing-workflow',
                    'transition' => 'existing-transition',
                    'entity_name' => \DateTime::class,
                ],
                'all-good' => [
                    ConfigObject::NAME_KEY => 'all-good',
                    'type' => WorkflowTransitMassActionExtension::MASS_ACTION_TYPE,
                    'workflow' => 'existing-workflow',
                    'transition' => 'existing-transition',
                    'entity_name' => \stdClass::class,
                ],
            ]
        ];
        $config = DatagridConfiguration::create($configArray);
        $otherAction = new AjaxMassAction();
        $otherAction->setOptions(ActionConfiguration::create($configArray['mass_actions']['other-action']));
        $noWorkflow = new WorkflowTransitMassAction();
        $noWorkflow->setOptions(ActionConfiguration::create($configArray['mass_actions']['no-workflow']));
        $noTransition = new WorkflowTransitMassAction();
        $noTransition->setOptions(ActionConfiguration::create($configArray['mass_actions']['no-transition']));
        $wrongEntity = new WorkflowTransitMassAction();
        $wrongEntity->setOptions(ActionConfiguration::create($configArray['mass_actions']['wrong-entity']));
        $allGood = new WorkflowTransitMassAction();
        $allGood->setOptions(ActionConfiguration::create($configArray['mass_actions']['all-good']));

        $this->actionMetadataFactory->expects($this->any())
            ->method('createActionMetadata')
            ->willReturnCallback(function (ActionInterface $action) {
                return $action->getOptions();
            });

        $this->actionFactory->expects($this->exactly(5))
            ->method('createAction')
            ->withConsecutive(
                ['other-action', $this->anything()],
                ['no-workflow', $this->anything()],
                ['no-transition', $this->anything()],
                ['wrong-entity', $this->anything()],
                ['all-good', $this->anything()]
            )
            ->willReturnOnConsecutiveCalls(
                $otherAction,
                $noWorkflow,
                $noTransition,
                $wrongEntity,
                $allGood
            );

        $workflow = $this->createMock(Workflow::class);
        $this->workflowRegistry->expects($this->exactly(4))
            ->method('getWorkflow')
            ->willReturnMap([
                ['not-existing-workflow', false, null],
                ['existing-workflow', false, $workflow],
            ]);

        $transitionManager = $this->createMock(TransitionManager::class);
        $workflow->expects($this->any())
            ->method('getTransitionManager')
            ->willReturn($transitionManager);
        $transitionManager->expects($this->exactly(3))
            ->method('getTransition')
            ->willReturnMap([
                ['not-existing-transition', null],
                ['existing-transition', $this->createMock(Transition::class)],
            ]);

        $this->entityClassResolver->expects($this->any())
            ->method('getEntityClass')
            ->willReturnArgument(0);

        $metadata = MetadataObject::create([]);
        $this->extension->visitMetadata($config, $metadata);
        self::assertEquals(
            [
                'other-action',
                'all-good',
            ],
            array_keys($metadata->offsetGet(MassActionExtension::METADATA_ACTION_KEY))
        );
    }
}
