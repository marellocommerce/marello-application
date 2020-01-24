<?php

namespace Marello\Bundle\NotificationBundle\Workflow;

use Marello\Bundle\NotificationBundle\Email\SendProcessor;
use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Action\ActionInterface;
use Oro\Component\ConfigExpression\ContextAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class SendNotificationAction extends AbstractAction
{
    /** @var PropertyPathInterface */
    protected $entity;

    /** @var PropertyPathInterface|string */
    protected $template;

    /** @var PropertyPathInterface|string */
    protected $recipients;
    
    /** @var SendProcessor */
    protected $sendProcessor;

    /**
     * SendNotificationAction constructor.
     *
     * @param ContextAccessor $contextAccessor
     * @param SendProcessor   $sendProcessor
     */
    public function __construct(ContextAccessor $contextAccessor, SendProcessor $sendProcessor)
    {
        parent::__construct($contextAccessor);

        $this->sendProcessor = $sendProcessor;
    }

    /**
     * @param mixed $context
     */
    protected function executeAction($context)
    {
        $entity     = $this->contextAccessor->getValue($context, $this->entity);
        $template   = $this->contextAccessor->getValue($context, $this->template);
        $recipients = $this->contextAccessor->getValue($context, $this->recipients);

        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        $this->sendProcessor->sendNotification($template, $recipients, $entity);
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
        if (!array_key_exists('entity', $options) && !$options['entity'] instanceof PropertyPathInterface) {
            throw new InvalidParameterException('Parameter "entity" is required.');
        } else {
            $this->entity = $this->getOption($options, 'entity');
        }

        if (!array_key_exists('template', $options)) {
            throw new InvalidParameterException('Parameter "template" is required.');
        } else {
            $this->template = $this->getOption($options, 'template');
        }

        $recipientsExist = array_key_exists('recipients', $options);
        $recipientExist  = array_key_exists('recipient', $options);

        if (!($recipientExist xor $recipientsExist)) {
            throw new InvalidParameterException('Either parameter "recipient" or parameter "recipients" is required.');
        } else {
            $this->recipients = $recipientsExist
                ? $this->getOption($options, 'recipients')
                : $this->getOption($options, 'recipient');
        }
    }
}
