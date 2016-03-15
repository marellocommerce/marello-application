<?php

namespace Marello\Bundle\OrderBundle\Workflow;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Model\Email\EmailSendProcessor;
use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Model\Action\AbstractAction;
use Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class SendNotificationEmailTemplateAction extends AbstractAction
{
    /** @var array */
    protected $options;

    /** @var EmailSendProcessor */
    protected $emailSendProcessor;

    public function __construct(ContextAccessor $contextAccessor, EmailSendProcessor $emailSendProcessor)
    {
        parent::__construct($contextAccessor);

        $this->emailSendProcessor = $emailSendProcessor;
    }


    /**
     * @param mixed $context
     */
    protected function executeAction($context)
    {
        /** @var Order $order */
        $order = $this->contextAccessor->getValue($context, $this->getOption($this->options, 'order'));

        /** @var string $template */
        $template = $this->getOption($this->options, 'template');

        $this->emailSendProcessor->sendMessage($order, $template);
    }

    /**
     * Initialize action based on passed options.
     *
     * @param array $options
     *
     * @return ActionInterface
     * @throws InvalidParameterException
     */
    public function initialize(array $options)
    {
        if (!isset($options['order']) || !$options['order'] instanceof PropertyPathInterface) {
            throw new InvalidParameterException('Parameter order is required.');
        }

        if (!isset($options['template'])) {
            throw new InvalidParameterException('Parameter template is required');
        }

        $this->options = $options;
    }
}
