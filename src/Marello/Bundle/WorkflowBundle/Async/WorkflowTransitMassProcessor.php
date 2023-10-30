<?php

namespace Marello\Bundle\WorkflowBundle\Async;

use Marello\Bundle\WorkflowBundle\Async\Topic\WorkflowTransitMassTopic;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Extension\MassAction\DTO\SelectedItems;
use Oro\Bundle\DataGridBundle\Extension\MassAction\IterableResultFactoryRegistry;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHelper;
use Oro\Bundle\FilterBundle\Grid\Extension\OrmFilterExtension;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Psr\Log\LoggerInterface;

use Marello\Bundle\WorkflowBundle\Manager\WorkflowTransitMassManager;

class WorkflowTransitMassProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var Manager
     */
    private $datagridManager;

    /**
     * @var MassActionHelper
     */
    private $massActionHelper;

    /**
     * @var IterableResultFactoryRegistry
     */
    private $iterableResultFactoryRegistry;

    /**
     * @var WorkflowTransitMassManager
     */
    private $workflowTransitMassManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Manager $datagridManager,
        MassActionHelper $massActionHelper,
        IterableResultFactoryRegistry $iterableResultFactoryRegistry,
        WorkflowTransitMassManager $workflowTransitMassManager,
        LoggerInterface $logger
    ) {
        $this->datagridManager = $datagridManager;
        $this->massActionHelper = $massActionHelper;
        $this->iterableResultFactoryRegistry = $iterableResultFactoryRegistry;
        $this->workflowTransitMassManager = $workflowTransitMassManager;
        $this->logger = $logger;
    }

    public static function getSubscribedTopics(): array
    {
        return [WorkflowTransitMassTopic::getName()];
    }

    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $data = JSON::decode($message->getBody());

        $datagridName = $data['datagridName'];
        $actionName = $data['actionName'];
        $parameters = $data['parameters'];
        $userEmail = $data['userEmail'];
        $batchSize = $data['batchSize'];

        $selectedItems = SelectedItems::createFromParameters($parameters);
        if ($selectedItems->isEmpty()) {
            return self::REJECT;
        }

        $filters = [];
        if (isset($parameters['filters'])) {
            $filters = $parameters['filters'];
        }

        try {
            $datagrid = $this->datagridManager->getDatagridByRequestParams($datagridName);
            $datagrid->getParameters()->mergeKey(OrmFilterExtension::FILTER_ROOT_PARAM, $filters);
            $options = $this->massActionHelper->getMassActionByName($actionName, $datagrid)->getOptions();

            $resultIterator = $this->iterableResultFactoryRegistry->createIterableResult(
                $datagrid->getAcceptedDatasource(),
                $options,
                $datagrid->getConfig(),
                $selectedItems
            );
            $resultIterator->setBufferSize($batchSize);

            $this->workflowTransitMassManager->doTransit($resultIterator, $options, $userEmail, true);
        } catch (\Exception $e) {
            $this->logger->error(
                'Unexpected exception occurred during workflow transition',
                ['exception' => $e]
            );

            return self::REJECT;
        }

        return self::ACK;
    }
}
