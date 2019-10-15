<?php

namespace Marello\Bridge\MarelloOroCommerceApi\Workflow\Action;

use Oro\Component\Action\Action\ActionInterface;
use Oro\Component\ConfigExpression\ExpressionInterface;

class SendEmailTemplateMarelloDecorator implements ActionInterface
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var ActionInterface
     */
    private $originalAction;

    public function __construct(ActionInterface $originalAction)
    {
        $this->originalAction = $originalAction;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($context)
    {
        if ($this->template !== 'order_confirmation_email') {
            $this->originalAction->execute($context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (!empty($options['template'])) {
            $this->template = $options['template'];
        }

        return $this->originalAction->initialize($options);
    }

    /**
     * {@inheritdoc}
     */
    public function setCondition(ExpressionInterface $condition)
    {
        $this->originalAction->setCondition($condition);
    }
}
