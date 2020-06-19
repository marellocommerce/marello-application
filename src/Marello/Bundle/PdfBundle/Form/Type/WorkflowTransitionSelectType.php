<?php

namespace Marello\Bundle\PdfBundle\Form\Type;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkflowTransitionSelectType extends AbstractType
{
    protected $workflowManager;

    public function __construct(WorkflowManager $workflowManager)
    {
        $this->workflowManager = $workflowManager;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('workflow')
            ->setDefault('choices', function (Options $options) {
                return $this->getWorkflowStepChoices($options['workflow']);
            })
            ->setDefaults([
                'required' => false,
            ])
        ;
    }

    protected function getWorkflowStepChoices($workflowName)
    {
        $workflow = $this->getWorkflow($workflowName);
        $configuration = $workflow->getDefinition()->getConfiguration();

        $choices = [];
        foreach (array_keys($configuration['transitions']) as $transition) {
            $choices[$this->formatTransitionName($transition)] = $transition;
        }

        return $choices;
    }

    protected function formatTransitionName($transition)
    {
        return ucfirst(str_replace('_', ' ', $transition));
    }

    protected function getWorkflow($workflowName)
    {
        return $this->workflowManager->getWorkflow($workflowName);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
