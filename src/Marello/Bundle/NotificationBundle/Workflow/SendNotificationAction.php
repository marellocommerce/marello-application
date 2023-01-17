<?php

namespace Marello\Bundle\NotificationBundle\Workflow;

use Symfony\Component\PropertyAccess\PropertyPathInterface;

use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Action\ActionInterface;
use Oro\Component\ConfigExpression\ContextAccessor;

use Marello\Bundle\NotificationBundle\Model\StringAttachment;
use Marello\Bundle\NotificationBundle\Provider\EmailSendProcessor;
use Marello\Bundle\NotificationBundle\Provider\NotificationAttachmentProvider;

class SendNotificationAction extends AbstractAction
{
    /** @var PropertyPathInterface */
    protected $entity;

    /** @var PropertyPathInterface|string */
    protected $template;

    /** @var PropertyPathInterface|string */
    protected $recipients;

    /** @var PropertyPathInterface|string */
    protected $attachments;

    /** @var PropertyPathInterface|string */
    protected $additionalContexts;

    /** @var EmailSendProcessor */
    protected $sendProcessor;

    /**
     * SendNotificationAction constructor.
     *
     * @param ContextAccessor $contextAccessor
     * @param EmailSendProcessor   $sendProcessor
     */
    public function __construct(
        ContextAccessor $contextAccessor,
        EmailSendProcessor $sendProcessor
    ) {
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
        $data       = $this->getData($context);

        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        $this->sendProcessor->sendNotification($template, $recipients, $entity, $data);
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

        if (array_key_exists(EmailSendProcessor::OPTION_ADDITIONAL_CONTEXTS, $options)) {
            $additionalContexts = $this->getOption($options, EmailSendProcessor::OPTION_ADDITIONAL_CONTEXTS);
            if (!is_array($additionalContexts)) {
                throw new InvalidParameterException('Parameter "additionalContexts" should be array');
            }

            $this->additionalContexts = $additionalContexts;
        }

        if (array_key_exists('attachments', $options)) {
            $attachments = $this->getOption($options, 'attachments');

            if (!is_array($attachments)) {
                throw new InvalidParameterException('Parameter "attachment" should be array');
            }

            foreach ($attachments as $key => $attachment) {
                if (!isset($attachment['filename'])) {
                    throw new InvalidParameterException(
                        sprintf('Parameter "filename" for attachment "%s" is required.', $key)
                    );
                }
                if (!isset($attachment['content'])) {
                    throw new InvalidParameterException(
                        sprintf('Parameter "content" for attachment "%s" is required.', $key)
                    );
                }
            }

            $this->attachments = $attachments;
        }
    }

    /**
     * @param $context
     * @return array
     */
    protected function getData($context)
    {
        $data = [];
        if ($this->attachments !== null) {
            $attachments = [];
            foreach ($this->attachments as $attachment) {
                $attachments = new StringAttachment(
                    $this->contextAccessor->getValue($context, 'filename'),
                    $this->contextAccessor->getValue($context, 'content')
                );
            }

            $data[NotificationAttachmentProvider::KEY_ATTACHMENTS] = $attachments;
        }

        if ($this->additionalContexts !== null) {
            foreach ($this->additionalContexts as $additionalContextPaths) {
                $additionalContext = $this
                    ->contextAccessor
                    ->getValue(
                        $context,
                        $additionalContextPaths
                    );
                $data[EmailSendProcessor::OPTION_ADDITIONAL_CONTEXTS][] = $additionalContext;
            }
        }

        return $data;
    }
}
