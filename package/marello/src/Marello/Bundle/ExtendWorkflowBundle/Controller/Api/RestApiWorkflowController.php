<?php

namespace Marello\Bundle\ExtendWorkflowBundle\Controller\Api;

use Marello\Bundle\ExtendWorkflowBundle\Api\Processor\Context\WorkflowContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\ApiBundle\Controller\AbstractRestApiController;
use Oro\Bundle\ApiBundle\Request\RestFilterValueAccessor;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestApiWorkflowController extends AbstractRestApiController
{
    /**
     * @ApiDoc(
     *     description="Start workflow for entity from transition",
     *     section="workflows",
     *     resource=true,
     *     views={"rest_json_api"}
     * )
     *
     * @param int $entityId
     * @param string $workflowName
     * @param string $transitionName
     * @param Request $request
     * @return Response
     */
    public function startAction($entityId, $workflowName, $transitionName, Request $request)
    {
        $processor = $this->get('marello_extend_workflow.api.workflow_start.action_processor');
        /** @var WorkflowContext $context */
        $context = $this->getContext($processor, $request);
        $context->setFilterValues(new RestFilterValueAccessor($request));
        $context->setId($entityId);
        $context->setWorkflowName($workflowName);
        $context->setTransitionName($transitionName);

        $processor->process($context);

        return $this->buildResponse($context);
    }
    
    /**
     * @ParamConverter("workflowItem", options={"id"="workflowItemId"})
     * @ApiDoc(
     *     description="Perform transition for workflow item",
     *     section="workflows",
     *     resource=true,
     *     views={"rest_json_api"}
     * )
     *
     * @param WorkflowItem $workflowItem
     * @param string $transitionName
     * @param Request $request
     * @return Response
     */
    public function transitAction(WorkflowItem $workflowItem, $transitionName, Request $request)
    {
        $processor = $this->get('marello_extend_workflow.api.workflow_transit.action_processor');
        /** @var WorkflowContext $context */
        $context = $this->getContext($processor, $request);
        $context->setFilterValues(new RestFilterValueAccessor($request));
        $context->setWorkflowItem($workflowItem);
        $context->setTransitionName($transitionName);

        $processor->process($context);

        return $this->buildResponse($context);
    }
    
    /**
     * @ParamConverter("workflowDefinition", options={"id"="workflowDefinitionId"})
     * @ApiDoc(
     *     description="Activate Workflow",
     *     section="workflows",
     *     resource=true,
     *     views={"rest_json_api"}
     * )
     *
     * @param WorkflowDefinition $workflowDefinition
     * @param Request $request
     * @return Response
     */
    public function activateAction(WorkflowDefinition $workflowDefinition, Request $request)
    {
        $processor = $this->get('marello_extend_workflow.api.workflow_activate.action_processor');
        /** @var WorkflowContext $context */
        $context = $this->getContext($processor, $request);
        $context->setFilterValues(new RestFilterValueAccessor($request));
        $context->setWorkflowDefinition($workflowDefinition);

        $processor->process($context);

        return $this->buildResponse($context);
    }
    
    /**
     * @ParamConverter("workflowDefinition", options={"id"="workflowDefinitionId"})
     * @ApiDoc(
     *     description="Deactivate Workflow",
     *     section="workflows",
     *     resource=true,
     *     views={"rest_json_api"}
     * )
     *
     * @param WorkflowDefinition $workflowDefinition
     * @param Request $request
     * @return Response
     */
    public function deactivateAction(WorkflowDefinition $workflowDefinition, Request $request)
    {
        $processor = $this->get('marello_extend_workflow.api.workflow_deactivate.action_processor');
        /** @var WorkflowContext $context */
        $context = $this->getContext($processor, $request);
        $context->setFilterValues(new RestFilterValueAccessor($request));
        $context->setWorkflowDefinition($workflowDefinition);

        $processor->process($context);

        return $this->buildResponse($context);
    }
}
