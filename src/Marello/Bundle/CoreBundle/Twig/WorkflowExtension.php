<?php

namespace Marello\Bundle\CoreBundle\Twig;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class WorkflowExtension extends \Twig_Extension
{
    const NAME = 'marello_workflow';
    
    /** @var WorkflowManager */
    protected $workflowManager;

    /**
     * ProductExtension constructor.
     *
     * @param WorkflowManager $workflowManager
     */
    public function __construct(WorkflowManager $workflowManager)
    {
        $this->workflowManager = $workflowManager;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'marello_core_get_current_workflow_steps',
                [$this, 'getCurrentWorkflowSteps']
            )
        ];
    }

    /**
     * @param Entity
     *
     * @return array
     */
    public function getCurrentWorkflowSteps($entity)
    {
        $steps = [];

        $workflowItems = $this->workflowManager->getWorkflowItemsByEntity($entity);
        foreach ($workflowItems as $workflowItem) {
            $steps[$workflowItem->getCurrentStep()->getDefinition()->getLabel()] = $workflowItem->getCurrentStep()->getLabel();
        }

        return $steps;
    }
}
