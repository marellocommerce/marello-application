<?php

namespace Marello\Bundle\CoreBundle\Twig;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WorkflowExtension extends AbstractExtension
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
            new TwigFunction(
                'marello_core_get_current_workflow_steps',
                [$this, 'getCurrentWorkflowSteps']
            )
        ];
    }

    /**
     * @param $entity
     *
     * @return array
     */
    public function getCurrentWorkflowSteps($entity)
    {
        $workflowItems = $this->workflowManager->getWorkflowItemsByEntity($entity);

        if (empty($workflowItems)) {
            return [];
        }

        $steps = [];
        foreach ($workflowItems as $workflowItem) {
            $currentStep = $workflowItem->getCurrentStep();
            $steps[$currentStep->getDefinition()->getLabel()] = $currentStep->getLabel();
        }

        return $steps;
    }
}
