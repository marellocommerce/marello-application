<?php

namespace Marello\Bundle\WorkflowBundle\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Gaufrette\Filesystem;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\DataGridBundle\Datasource\Orm\IterableResultInterface;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\NotificationBundle\Async\Topics;
use Oro\Bundle\NotificationBundle\Model\NotificationSettings;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Component\Routing\RouterInterface;

class WorkflowTransitMassManager
{
    /**
     * @var WorkflowManager
     */
    private $workflowManager;

    /**
     * @var WorkflowRegistry
     */
    private $workflowRegistry;

    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var MessageProducerInterface
     */
    private $messageProducer;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var NotificationSettings
     */
    private $notificationSettings;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        WorkflowManager $workflowManager,
        WorkflowRegistry $workflowRegistry,
        ManagerRegistry $registry,
        MessageProducerInterface $messageProducer,
        ConfigManager $configManager,
        NotificationSettings $notificationSettings,
        RouterInterface $router,
        FilesystemMap $filesystemMap
    ) {
        $this->workflowManager = $workflowManager;
        $this->workflowRegistry = $workflowRegistry;
        $this->registry = $registry;
        $this->messageProducer = $messageProducer;
        $this->configManager = $configManager;
        $this->notificationSettings = $notificationSettings;
        $this->router = $router;
        $this->filesystem = $filesystemMap->get('importexport');
    }

    public function doTransit(
        IterableResultInterface $result,
        ActionConfiguration $options,
        string $userEmail,
        bool $alwaysSendReport = false
    ): array {
        $entityName = $options->offsetGet('entity_name');
        $workflowName = $options->offsetGet('workflow');
        $transition = $options->offsetGet('transition');
        $entityIdentifierField = $this->getEntityIdentifierField($options);
        $manager = $this->registry->getManagerForClass($entityName);

        $totalCount = 0;
        $successCount = 0;
        $withoutWorkflowItems = [];
        $transitionIsNotAllowed = [];
        $transitionFailed = [];
        foreach ($result as $record) {
            $totalCount++;
            $entity = $record->getRootEntity();
            $identifierValue = $record->getValue($entityIdentifierField);
            if (!$entity) {
                $entity = $manager->getReference($entityName, $identifierValue);
            }

            $result = $this->transitItem(
                $entity,
                $workflowName,
                $transition,
                $identifierValue,
                $withoutWorkflowItems,
                $transitionIsNotAllowed,
                $transitionFailed
            );

            if ($result) {
                $successCount++;
            }
        }

        $manager->flush();

        if ($alwaysSendReport || $successCount < $totalCount) {
            $logUrl = $this->generateLogFile(
                $totalCount,
                $successCount,
                $withoutWorkflowItems,
                $transitionIsNotAllowed,
                $transitionFailed
            );
            $this->sendEmailReport(
                $userEmail,
                $entityName,
                $totalCount,
                $successCount,
                $logUrl,
                $options->offsetGet('report_template')
            );
        }

        return [$totalCount, $successCount];
    }

    protected function transitItem(
        $entity,
        string $workflowName,
        string $transition,
        $identifierValue,
        array &$withoutWorkflowItems,
        array &$transitionIsNotAllowed,
        array &$transitionFailed
    ): bool {
        try {
            $workflowItem = $this->workflowManager->getWorkflowItem($entity, $workflowName);
            if (!$workflowItem) {
                $withoutWorkflowItems[] = $identifierValue;
                return false;
            }

            $workflow = $this->workflowRegistry->getWorkflow($workflowItem->getWorkflowName());
            $isAllowed = $workflow->isTransitionAllowed($workflowItem, $transition);
            if (!$isAllowed) {
                $transitionIsNotAllowed[] = $identifierValue;
                return false;
            }

            $workflow->transit($workflowItem, $transition);
        } catch (WorkflowException $e) {
            $transitionFailed[$identifierValue] = $e->getMessage();
            return false;
        }

        $workflowItem->setUpdated();

        return true;
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

    protected function sendEmailReport(
        string $userEmail,
        string $entityName,
        int $totalCount,
        int $successCount,
        string $logUrl,
        string $reportTemplate
    ) {
        $sender = $this->notificationSettings->getSender();
        $this->messageProducer->send(
            Topics::SEND_NOTIFICATION_EMAIL,
            [
                'sender' => $sender->toArray(),
                'toEmail' => $userEmail,
                'body' => [
                    'entityName' => $entityName,
                    'totalCount' => $totalCount,
                    'successCount' => $successCount,
                    'errorLogUrl' => $logUrl,
                ],
                'contentType' => 'text/html',
                'template' => $reportTemplate,
            ]
        );
    }

    protected function generateLogFile(
        int $totalCount,
        int $successCount,
        array $withoutWorkflowItems,
        array $transitionIsNotAllowed,
        array $transitionFailed
    ): string {
        $content = 'Total records: ' . $totalCount . PHP_EOL;
        $content .= 'Successful records: ' . $successCount . PHP_EOL;
        if ($withoutWorkflowItems) {
            $content .= PHP_EOL . 'Workflow is not started for the following records (identifiers):' . PHP_EOL;
            foreach ($withoutWorkflowItems as $identifier) {
                $content .= '  - ' . $identifier . PHP_EOL;
            }
        }
        if ($transitionIsNotAllowed) {
            $content .= PHP_EOL . 'Transition is not allowed for the following records (identifiers):' . PHP_EOL;
            foreach ($transitionIsNotAllowed as $identifier) {
                $content .= '  - ' . $identifier . PHP_EOL;
            }
        }
        if ($transitionFailed) {
            $content .= PHP_EOL . 'Transition was failed for the following records (identifiers):' . PHP_EOL;
            foreach ($transitionFailed as $identifier => $reason) {
                $content .= '  - ' . $identifier . ': ' . $reason . PHP_EOL;
            }
        }

        $hash = md5(microtime(true));
        $this->filesystem->write($hash, $content);

        return $this->configManager->get('oro_ui.application_url')
            . $this->router->generate('marello_workflow_mass_action_log', ['hash' => $hash]);
    }
}
