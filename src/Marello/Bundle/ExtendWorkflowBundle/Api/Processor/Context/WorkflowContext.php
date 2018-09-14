<?php

namespace Marello\Bundle\ExtendWorkflowBundle\Api\Processor\Context;

use Oro\Bundle\ApiBundle\Processor\SingleItemContext;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class WorkflowContext extends SingleItemContext
{
    const WORKFLOW_NAME = 'workflow_name';
    const TRANSITION_NAME = 'transition_name';
    const WORKFLOW_ITEM = 'workflow_item';
    const WORKFLOW_DEFINITION = 'workflow_definition';

    /**
     * @return string
     */
    public function getWorkflowName()
    {
        return $this->get(self::WORKFLOW_NAME);
    }

    /**
     * @param string $workflowName
     * @return $this
     */
    public function setWorkflowName($workflowName)
    {
        $this->set(self::WORKFLOW_NAME, $workflowName);
        
        return $this;
    }

    /**
     * @return string
     */
    public function getTransitionName()
    {
        return $this->get(self::TRANSITION_NAME);
    }

    /**
     * @param string $transitionName
     * @return $this
     */
    public function setTransitionName($transitionName)
    {
        $this->set(self::TRANSITION_NAME, $transitionName);

        return $this;
    }
    
    /**
     * @return WorkflowItem|null
     */
    public function getWorkflowItem()
    {
        return $this->get(self::WORKFLOW_ITEM);
    }

    /**
     * @param WorkflowItem $workflowItem
     * @return $this
     */
    public function setWorkflowItem(WorkflowItem $workflowItem)
    {
        $this->set(self::WORKFLOW_ITEM, $workflowItem);

        return $this;
    }
    
    /**
     * @return WorkflowDefinition|null
     */
    public function getWorkflowDefifition()
    {
        return $this->get(self::WORKFLOW_DEFINITION);
    }

    /**
     * @param WorkflowDefinition $workflowDefinition
     * @return $this
     */
    public function setWorkflowDefinition(WorkflowDefinition $workflowDefinition)
    {
        $this->set(self::WORKFLOW_DEFINITION, $workflowDefinition);

        return $this;
    }
}
