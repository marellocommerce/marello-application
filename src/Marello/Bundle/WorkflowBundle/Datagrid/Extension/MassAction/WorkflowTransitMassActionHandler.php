<?php

namespace Marello\Bundle\WorkflowBundle\Datagrid\Extension\MassAction;

use Marello\Bundle\WorkflowBundle\Async\Topic\WorkflowTransitMassTopic;
use Marello\Bundle\WorkflowBundle\Manager\WorkflowTransitMassManager;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\IterableResult;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerArgs;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WorkflowTransitMassActionHandler implements MassActionHandlerInterface
{
    /** @var WorkflowTransitMassManager */
    protected $workflowTransitMassManager;

    /** @var MessageProducerInterface */
    protected $messageProducer;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var RequestStack */
    protected $requestStack;

    /** @var MassActionParametersParser */
    protected $massActionParametersParser;

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(
        WorkflowTransitMassManager $workflowTransitMassManager,
        MessageProducerInterface $messageProducer,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        MassActionParametersParser $massActionParametersParser,
        TranslatorInterface $translator
    ) {
        $this->workflowTransitMassManager = $workflowTransitMassManager;
        $this->messageProducer = $messageProducer;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->massActionParametersParser = $massActionParametersParser;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(MassActionHandlerArgs $args)
    {
        $options = $args->getMassAction()->getOptions();
        $batchSize = $options->offsetGet('batch_size');

        $result = $args->getResults();
        if (!$result instanceof IterableResult) {
            throw new \LogicException(sprintf(
                'Wrong result object. Instance of %s expected, got %s',
                IterableResult::class,
                get_class($result)
            ));
        }

        $totalCount = $result->count();
        if ($result->count() > $batchSize) {
            return $this->handleAsync($args->getDatagrid(), $args->getMassAction(), $totalCount, $batchSize);
        }

        return $this->handleSync($result, $options);
    }

    protected function handleAsync(
        DatagridInterface $datagrid,
        MassActionInterface $massAction,
        int $totalCount,
        int $batchSize
    ): MassActionResponse {
        $request = $this->requestStack->getCurrentRequest();
        $parameters = $this->massActionParametersParser->parse($request);

        $this->messageProducer->send(WorkflowTransitMassTopic::getName(), [
            'datagridName' => $datagrid->getName(),
            'actionName' => $massAction->getName(),
            'parameters' => $parameters,
            'userEmail' => $this->getUserEmail(),
            'batchSize' => $batchSize,
        ]);

        return new MassActionResponse(
            true,
            $this->translator->trans('marello.workflow.mass_action.workflow_transit.async'),
            [
                '%total%' => $totalCount,
            ]
        );
    }

    protected function handleSync(IterableResult $result, ActionConfiguration $options): MassActionResponse
    {
        [$totalCount, $successCount] = $this->workflowTransitMassManager->doTransit(
            $result,
            $options,
            $this->getUserEmail()
        );

        if ($successCount === 0) {
            $message = 'marello.workflow.mass_action.workflow_transit.sync.failed';
        } elseif ($totalCount === $successCount) {
            $message = 'marello.workflow.mass_action.workflow_transit.sync.success';
        } else {
            $message = 'marello.workflow.mass_action.workflow_transit.sync.partly';
        }

        return new MassActionResponse(
            $successCount > 0,
            $this->translator->trans(
                $message,
                [
                    '%total%' => $totalCount,
                    '%success%' => $successCount,
                ]
            )
        );
    }

    protected function getUserEmail(): string
    {
        /** @var EmailHolderInterface $user */
        $user = $this->tokenStorage->getToken()->getUser();

        return $user->getEmail();
    }
}
