<?php

namespace Marello\Bundle\ExtendWorkflowBundle\Api\Processor;

use FOS\RestBundle\Util\Codes;
use Marello\Bundle\ExtendWorkflowBundle\Api\Processor\Context\WorkflowContext;
use Oro\Bundle\ApiBundle\Model\Error;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowNotFoundException;
use Oro\Bundle\WorkflowBundle\Helper\WorkflowTranslationHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ProcessWorkflowTransit implements ProcessorInterface
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
     * @param WorkflowManager $workflowManager
     * @param TranslatorInterface $translator
     */
    public function __construct(WorkflowManager $workflowManager, TranslatorInterface $translator)
    {
        $this->workflowManager = $workflowManager;
        $this->translator = $translator;
    }
    
    /**
     * @inheritDoc
     */
    public function process(ContextInterface $context)
    {
        /** @var WorkflowContext $context */
        $workflowItem = $context->getWorkflowItem();
        $transitionName = $context->getTransitionName();
        try {
            $this->workflowManager->transit($workflowItem, $transitionName);
            $currentStep = $workflowItem->getCurrentStep();
            $context->setResult([
                'workflowItem' => $workflowItem->getId(),
                'currentStep' => [
                        'name' => $currentStep->getName(),
                        'label' => $this->translator->trans(
                            $currentStep->getLabel(),
                            [],
                            WorkflowTranslationHelper::TRANSLATION_DOMAIN
                        )
                    ]
                ]);
        } catch (WorkflowNotFoundException $e) {
            $context->addError(Error::create($e->getMessage())->setStatusCode(Codes::HTTP_NOT_FOUND));
        } catch (InvalidTransitionException $e) {
            $context->addError(Error::create($e->getMessage())->setStatusCode(Codes::HTTP_BAD_REQUEST));
        } catch (ForbiddenTransitionException $e) {
            $context->addError(Error::create($e->getMessage())->setStatusCode(Codes::HTTP_FORBIDDEN));
        } catch (\Exception $e) {
            $context->addError(Error::create($e->getMessage())->setStatusCode(Codes::HTTP_INTERNAL_SERVER_ERROR));
        }
    }
}
