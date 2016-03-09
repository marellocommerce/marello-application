<?php

namespace Marello\Bundle\OrderBundle\Workflow;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EmailBundle\Mailer\Processor;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\EmailBundle\Tools\EmailAddressHelper;
use Oro\Bundle\EmailBundle\Workflow\Action\SendEmailTemplate;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SendNotificationEmailTemplateAction extends SendEmailTemplate
{
    /** @var ConfigManager */
    private $cm;

    public function __construct(
        ContextAccessor $contextAccessor,
        Processor $emailProcessor,
        EmailAddressHelper $emailAddressHelper,
        EntityNameResolver $entityNameResolver,
        EmailRenderer $renderer,
        ObjectManager $objectManager,
        ValidatorInterface $validator,
        ConfigManager $cm
    ) {
        parent::__construct(
            $contextAccessor,
            $emailProcessor,
            $emailAddressHelper,
            $entityNameResolver,
            $renderer,
            $objectManager,
            $validator
        );

        $this->cm = $cm;
    }

    public function initialize(array $options)
    {
        if (empty($options['from'])) {
            $options['from'] = [
                'name'  => $this->cm->get('oro_notification.email_notification_sender_name'),
                'email' => $this->cm->get('oro_notification.email_notification_sender_email'),
            ];
        }

        return parent::initialize($options);
    }


}
