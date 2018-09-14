<?php

namespace Marello\Bundle\ExtendWorkflowBundle\Api\Processor;

use FOS\RestBundle\Util\Codes;
use Marello\Bundle\ExtendWorkflowBundle\Api\Processor\Context\WorkflowContext;
use Oro\Bundle\ActionBundle\Provider\ButtonSearchContextProvider;
use Oro\Bundle\ApiBundle\Model\Error;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowNotFoundException;
use Oro\Bundle\WorkflowBundle\Helper\WorkflowTranslationHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Translation\TranslatorInterface;

class ProcessWorkflowStart implements ProcessorInterface
{
    /**
     * @var WorkflowManager
     */
    private $workflowManager;

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var WorkflowAwareSerializer
     */
    private $serializer;

    /**
     * @var ButtonSearchContextProvider
     *
     */
    private $buttonSearchContextProvider;
    
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param WorkflowManager $workflowManager
     * @param DoctrineHelper $doctrineHelper
     * @param WorkflowAwareSerializer $serializer
     * @param ButtonSearchContextProvider $buttonSearchContextProvider
     * @param TranslatorInterface $translator
     */
    public function __construct(
        WorkflowManager $workflowManager,
        DoctrineHelper $doctrineHelper,
        WorkflowAwareSerializer $serializer,
        ButtonSearchContextProvider $buttonSearchContextProvider,
        TranslatorInterface $translator
    ) {
        $this->workflowManager = $workflowManager;
        $this->doctrineHelper = $doctrineHelper;
        $this->serializer = $serializer;
        $this->buttonSearchContextProvider = $buttonSearchContextProvider;
        $this->translator = $translator;
    }
    
    /**
     * @inheritDoc
     */
    public function process(ContextInterface $context)
    {
        /** @var WorkflowContext $context */

        if (!$entityId = $context->getId()) {
            $context->addError(Error::create('There is no entityId provided')
                ->setStatusCode(Codes::HTTP_INTERNAL_SERVER_ERROR));
            return null;
        }

        try {
            $entityId = $context->getId();
            $workflowName = $context->getWorkflowName();
            $transitionName = $context->getTransitionName();
            $data = $context->get('data');
            $dataArray = [];
            if ($data) {
                $this->serializer->setWorkflowName($workflowName);

                $data = $this->serializer->deserialize(
                    $data,
                    WorkflowData::class,
                    'json'
                );
                $dataArray = $data->getValues();
            }

            $workflow = $this->workflowManager->getWorkflow($workflowName);
            $entityClass = $workflow->getDefinition()->getRelatedEntity();

            $transition = $workflow->getTransitionManager()->getTransition($transitionName);
            if (!$transition) {
                $context
                    ->addError(Error::create(
                        sprintf('There is no transition %s for %s workflow', $transitionName, $workflowName)
                    )
                    ->setStatusCode(Codes::HTTP_INTERNAL_SERVER_ERROR));
                return null;
            }
            if (!$transition->isEmptyInitOptions()) {
                $contextAttribute = $transition->getInitContextAttribute();
                $dataArray[$contextAttribute] = $this->buttonSearchContextProvider->getButtonSearchContext();
                $entityId = null;
            }

            $entity = $this->getEntityReference($entityClass, $entityId);
            $workflowItem = $this->workflowManager->startWorkflow($workflowName, $entity, $transitionName, $dataArray);
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
        } catch (HttpException $e) {
            $context->addError(Error::create($e->getMessage())->setStatusCode($e->getStatusCode()));
        } catch (WorkflowNotFoundException $e) {
            $context->addError(Error::create($e->getMessage())->setStatusCode(Codes::HTTP_NOT_FOUND));
        } catch (UnknownAttributeException $e) {
            $context->addError(Error::create($e->getMessage())->setStatusCode(Codes::HTTP_BAD_REQUEST));
        } catch (InvalidTransitionException $e) {
            $context->addError(Error::create($e->getMessage())->setStatusCode(Codes::HTTP_BAD_REQUEST));
        } catch (ForbiddenTransitionException $e) {
            $context->addError(Error::create($e->getMessage())->setStatusCode(Codes::HTTP_FORBIDDEN));
        } catch (\Exception $e) {
            $context->addError(Error::create($e->getMessage())->setStatusCode(Codes::HTTP_INTERNAL_SERVER_ERROR));
        }
    }
    
    /**
     * @param string $entityClass
     * @param mixed $entityId
     * @return mixed
     */
    protected function getEntityReference($entityClass, $entityId)
    {
        return $this->doctrineHelper->getEntityReference($entityClass, $entityId);
    }
}
