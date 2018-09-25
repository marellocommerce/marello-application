<?php

namespace Marello\Bundle\ExtendWorkflowBundle\Api\Processor;

use FOS\RestBundle\Util\Codes;
use Marello\Bundle\ExtendWorkflowBundle\Api\Processor\Context\WorkflowContext;
use Oro\Bundle\ApiBundle\Model\Error;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowNotFoundException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ProcessWorkflowToggle implements ProcessorInterface
{
    /**
     * @var WorkflowManager
     */
    private $workflowManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $action;

    /**
     * @param WorkflowManager $workflowManager
     * @param TranslatorInterface $translator
     * @param string $action
     */
    public function __construct(
        WorkflowManager $workflowManager,
        TranslatorInterface $translator,
        $action
    ) {
        $this->workflowManager = $workflowManager;
        $this->translator = $translator;
        $this->action = $action;
    }
    
    /**
     * @inheritDoc
     */
    public function process(ContextInterface $context)
    {
        /** @var WorkflowContext $context */
        $workflowDefinition = $context->getWorkflowDefifition();

        $this->workflowManager->resetWorkflowData($workflowDefinition->getName());
        if ($this->action === 'activate') {
            $this->workflowManager->activateWorkflow($workflowDefinition->getName());
            $message = $this->translator->trans('Workflow activated');
        } else {
            $this->workflowManager->deactivateWorkflow($workflowDefinition->getName());
            $message = $this->translator->trans('Workflow deactivated');
        }

        $context->setResult([
            'successful' => true,
            'message' => $message
        ]);
    }
}
