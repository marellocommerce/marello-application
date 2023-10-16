<?php

namespace Marello\Bundle\NotificationBundle\Provider;

use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Psr\Log\LoggerAwareTrait;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Email as EmailConstraints;

use Oro\Bundle\EmailBundle\Form\Model\Email;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\EmailBundle\Sender\EmailModelSender;
use Oro\Bundle\EmailBundle\Tools\EmailOriginHelper;
use Oro\Bundle\EmailBundle\Tools\EmailAddressHelper;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\NotificationBundle\Model\NotificationSettings;
use Oro\Bundle\EmailBundle\Tools\EmailAttachmentTransformer;

use Marello\Bundle\LocaleBundle\Manager\EmailTemplateManager;
use Marello\Bundle\NotificationBundle\Exception\MarelloNotificationException;

class EmailSendProcessor
{
    use LoggerAwareTrait;

    const OPTION_ATTACHMENTS = 'attachments';
    const OPTION_ADDITIONAL_CONTEXTS = 'additionalContexts';

    /** @var EmailConstraints $emailConstraint */
    protected $emailConstraint;

    /** @var EmailRenderer $renderer */
    protected $renderer;

    /** @var ValidatorInterface $validator */
    protected $validator;

    /** @var EmailOriginHelper $emailOriginHelper */
    protected $emailOriginHelper;

    /** @var EmailModelSender $emailModelSender */
    protected $emailModelSender;

    /** @var EmailAddressHelper $emailAddressHelper */
    protected $emailAddressHelper;

    /** @var EmailTemplateManager $emailTemplateManager */
    protected $emailTemplateManager;

    /** @var NotificationSettings $notificationSettings */
    private $notificationSettings;

    /** @var EmailAttachmentTransformer $emailAttachmentTransformer */
    protected $emailAttachmentTransformer;

    /**
     * AttachmentEmailSendProcessor constructor.
     * @param EmailModelSender $emailModelSender
     * @param EmailAddressHelper $emailAddressHelper
     * @param ValidatorInterface $validator
     * @param EmailOriginHelper $emailOriginHelper
     * @param EmailRenderer $renderer
     * @param EmailTemplateManager $emailTemplateManager
     * @param NotificationSettings $notificationSettings
     */
    public function __construct(
        EmailModelSender $emailModelSender,
        EmailAddressHelper $emailAddressHelper,
        ValidatorInterface $validator,
        EmailOriginHelper $emailOriginHelper,
        EmailRenderer $renderer,
        EmailTemplateManager $emailTemplateManager,
        NotificationSettings $notificationSettings,
        EmailAttachmentTransformer $emailAttachmentTransformer
    ) {
        $this->validator = $validator;
        $this->renderer = $renderer;
        $this->emailOriginHelper = $emailOriginHelper;
        $this->emailModelSender = $emailModelSender;
        $this->emailAddressHelper = $emailAddressHelper;
        $this->emailTemplateManager = $emailTemplateManager;
        $this->notificationSettings = $notificationSettings;
        $this->emailAttachmentTransformer = $emailAttachmentTransformer;
        $this->emailConstraint = new EmailConstraints(['message' => 'Invalid email address']);
    }

    /**
     * @param $templateName
     * @param array $recipients
     * @param $entity
     * @param array $data
     * @throws MarelloNotificationException
     * @throws \Twig\Error\Error
     */
    public function sendNotification($templateName, array $recipients, $entity, array $data = [])
    {
        $emailModel = new Email();
        $from = $this->getFormattedSender();
        $this->validateAddress($from);
        $emailModel->setFrom($from);
        $to = [];

        foreach ($recipients as $recipient) {
            if ($recipient) {
                $address = $this->getEmailAddress($recipient);
                $this->validateAddress($address);
                $to[] = $address;
            }
        }
        $emailModel->setTo($to);
        $template = $this->emailTemplateManager->findTemplate($templateName, $entity);
        /*
         * If template is not found, throw an exception.
         */
        if ($template === null) {
            throw new MarelloNotificationException(
                sprintf(
                    'Email template with name "%s" for entity "%s" was not found. Check if such template exists.',
                    $templateName,
                    \get_class($entity)
                )
            );
        }

        // set type earlier otherwise it will not render correctly as html...
        $emailModel->setType($template->getType());
        if ($templateModel = $this->emailTemplateManager->getLocalizedModel($template, $entity)) {
            $template = $templateModel;
        }

        if ($entity instanceof OrganizationAwareInterface) {
            $emailModel->setOrganization($entity->getOrganization());
        } elseif (isset($recipient) && $recipient instanceof OrganizationAwareInterface) {
            $emailModel->setOrganization($recipient->getOrganization());
        }

        $templateData = $this->renderer->compileMessage($template, compact('entity'));
        list ($subjectRendered, $templateRendered) = $templateData;

        $emailModel->setSubject($subjectRendered);
        $emailModel->setBody($templateRendered);
        $this->addAttachments($emailModel, $data);
        $contexts = $this->getAdditionalContexts($entity, $data);
        $emailModel->setContexts($contexts);
        try {
            $emailOrigin = $this->emailOriginHelper->getEmailOrigin(
                $emailModel->getFrom(),
                $emailModel->getOrganization()
            );

            $this->emailModelSender->send($emailModel, $emailOrigin);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('Workflow send email template action.', ['exception' => $exception]);
        }

        $this->logger->info('Workflow send email template successful .', []);
    }

    /**
     * @param $entity
     * @param $data
     * @return array
     */
    protected function getAdditionalContexts($entity, $data)
    {
        $contexts = [$entity];
        if (isset($data[self::OPTION_ADDITIONAL_CONTEXTS]) || !empty($data[self::OPTION_ADDITIONAL_CONTEXTS])) {
            foreach ($data[self::OPTION_ADDITIONAL_CONTEXTS] as $additionalContext) {
                $contexts[] = $additionalContext;
            }
        }

        return $contexts;
    }

    /**
     * @param Email $emailModel
     * @param $data
     */
    protected function addAttachments(Email $emailModel, $data)
    {
        if (isset($data[self::OPTION_ATTACHMENTS]) || !empty($data[self::OPTION_ATTACHMENTS])) {
            $attachments = $data[self::OPTION_ATTACHMENTS];
            foreach ($attachments as $attachment) {
                $emailAttachmentEntity = $this->emailAttachmentTransformer->attachmentEntityToEntity($attachment);
                $emailAttachment = $this->emailAttachmentTransformer->attachmentEntityToModel($attachment);
                $emailAttachment->setEmailAttachment($emailAttachmentEntity);
                $emailModel->addAttachment($emailAttachment);
            }
        }
    }

    /**
     * @param string $email
     * @throws ValidatorException
     */
    protected function validateAddress($email): void
    {
        $errorList = $this->validator->validate($email, $this->emailConstraint);

        if ($errorList && $errorList->count() > 0) {
            throw new ValidatorException($errorList->get(0)->getMessage());
        }
    }

    /**
     * Get formatted From email address from Notification settings
     * @return string
     */
    private function getFormattedSender(): string
    {
        $sendFromSettings = $this->notificationSettings->getSender();
        list ($email, $name) = $sendFromSettings->toArray();
        return $this->emailAddressHelper->buildFullEmailAddress($email, $name);
    }

    /**
     * Get email address prepared for sending.
     *
     * @param mixed $recipient
     *
     * @return string
     */
    protected function getEmailAddress($recipient)
    {
        $name = null;
        if (is_string($recipient)) {
            $name = $email = $recipient;
        }

        if (is_object($recipient) && $recipient instanceof EmailHolderInterface) {
            $name = $email = $recipient->getEmail();
        }

        $emailAddress = $this->emailAddressHelper->extractPureEmailAddress($email);
        $name = $this->emailAddressHelper->extractEmailAddressName($name);

        return $this->emailAddressHelper->buildFullEmailAddress($emailAddress, $name);
    }
}
