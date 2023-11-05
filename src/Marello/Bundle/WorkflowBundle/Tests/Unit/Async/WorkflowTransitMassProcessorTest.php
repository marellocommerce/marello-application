<?php

namespace Marello\Bundle\WorkflowBundle\Tests\Unit\Async;

use Marello\Bundle\WorkflowBundle\Async\Topic\WorkflowTransitMassTopic;
use Marello\Bundle\WorkflowBundle\Async\WorkflowTransitMassProcessor;
use Marello\Bundle\WorkflowBundle\Manager\WorkflowTransitMassManager;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\IterableResultInterface;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\IterableResultFactoryRegistry;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHelper;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\Dbal\DbalMessage;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class WorkflowTransitMassProcessorTest extends TestCase
{
    /**
     * @var Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $datagridManager;

    /**
     * @var MassActionHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $massActionHelper;

    /**
     * @var IterableResultFactoryRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $iterableResultFactoryRegistry;

    /**
     * @var WorkflowTransitMassManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $workflowTransitMassManager;

    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var WorkflowTransitMassProcessor
     */
    private $processor;

    protected function setUp(): void
    {
        $this->datagridManager = $this->createMock(Manager::class);
        $this->massActionHelper = $this->createMock(MassActionHelper::class);
        $this->iterableResultFactoryRegistry = $this->createMock(IterableResultFactoryRegistry::class);
        $this->workflowTransitMassManager = $this->createMock(WorkflowTransitMassManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->processor = new WorkflowTransitMassProcessor(
            $this->datagridManager,
            $this->massActionHelper,
            $this->iterableResultFactoryRegistry,
            $this->workflowTransitMassManager,
            $this->logger
        );
    }

    public function testGetSubscribedTopics()
    {
        $this->assertEquals(
            [WorkflowTransitMassTopic::getName()],
            WorkflowTransitMassProcessor::getSubscribedTopics()
        );
    }

    public function testProcessWhenNoSelectedItems()
    {
        $session = $this->createMock(SessionInterface::class);
        $message = new DbalMessage();
        $message->setBody(JSON::encode([
            'datagridName' => 'test-grid',
            'actionName' => 'test-action',
            'parameters' => [
                'inset' => true,
                'values' => [],
            ],
            'userEmail' => 'test@test.com',
            'batchSize' => 5,
        ]));

        $this->assertEquals(MessageProcessorInterface::REJECT, $this->processor->process($message, $session));
    }

    public function testProcessWhenError()
    {
        $session = $this->createMock(SessionInterface::class);
        $message = new DbalMessage();
        $message->setBody(JSON::encode([
            'datagridName' => 'test-grid',
            'actionName' => 'test-action',
            'parameters' => [
                'inset' => false,
                'values' => [],
            ],
            'userEmail' => 'test@test.com',
            'batchSize' => 5,
        ]));

        $this->datagridManager->expects($this->once())
            ->method('getDatagridByRequestParams')
            ->willThrowException(new \Exception());
        $this->logger->expects($this->once())
            ->method('error');

        $this->assertEquals(MessageProcessorInterface::REJECT, $this->processor->process($message, $session));
    }

    public function testProcess()
    {
        $datagridName = 'test-grid';
        $actionName = 'test-action';
        $userEmail = 'test@test.com';

        $session = $this->createMock(SessionInterface::class);
        $message = new DbalMessage();
        $message->setBody(JSON::encode([
            'datagridName' => $datagridName,
            'actionName' => $actionName,
            'parameters' => [
                'inset' => false,
                'values' => [],
            ],
            'userEmail' => $userEmail,
            'batchSize' => 5,
        ]));

        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects($this->any())
            ->method('getParameters')
            ->willReturn(new ParameterBag());
        $datagrid->expects($this->any())
            ->method('getAcceptedDatasource')
            ->willReturn($this->createMock(DatasourceInterface::class));
        $datagrid->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->createMock(DatagridConfiguration::class));
        $this->datagridManager->expects($this->once())
            ->method('getDatagridByRequestParams')
            ->with($datagridName)
            ->willReturn($datagrid);
        $action = $this->createMock(MassActionInterface::class);
        $options = $this->createMock(ActionConfiguration::class);
        $action->expects($this->any())
            ->method('getOptions')
            ->willReturn($options);
        $this->massActionHelper->expects($this->once())
            ->method('getMassActionByName')
            ->with($actionName, $datagrid)
            ->willReturn($action);
        $iterator = $this->createMock(IterableResultInterface::class);
        $this->iterableResultFactoryRegistry->expects($this->once())
            ->method('createIterableResult')
            ->willReturn($iterator);
        $this->workflowTransitMassManager->expects($this->once())
            ->method('doTransit')
            ->with($iterator, $options, $userEmail, true);
        $this->logger->expects($this->never())
            ->method('error');

        $this->assertEquals(MessageProcessorInterface::ACK, $this->processor->process($message, $session));
    }
}
