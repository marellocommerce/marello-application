<?php

namespace Marello\Bundle\ExtendWorkflowBundle\Button;

trait TransitionButtonTemplateTrait
{
    protected $defaultTemplate = 'MarelloExtendWorkflowBundle::Button\transitionButton.html.twig';

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return $this->defaultTemplate;
    }
}
