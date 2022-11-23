<?php

namespace Marello\Bundle\PdfBundle\Workflow\Action;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;

use Liip\ImagineBundle\Binary\MimeTypeGuesserInterface;
use Oro\Bundle\EmailBundle\Sender\EmailModelSender;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Validator\Constraints\Email as EmailConstraints;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Oro\Bundle\EmailBundle\Entity\EmailAttachmentContent;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\EmailBundle\Entity\EmailAttachment as AttachmentEntity;
use Oro\Bundle\EmailBundle\Form\Model\Email;
use Oro\Bundle\EmailBundle\Form\Model\EmailAttachment;
use Oro\Bundle\EmailBundle\Workflow\Action\AbstractSendEmail;
use Oro\Component\Action\Exception\InvalidArgumentException;
use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Bundle\EmailBundle\Tools\EmailOriginHelper;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\EmailBundle\Tools\EmailAddressHelper;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Component\ConfigExpression\ContextAccessor;

class SendEmailTemplateAttachmentAction extends AbstractSendEmail
{
    const OPTION_ATTACHMENTS = 'attachments';
    const OPTION_ATTACHMENT_BODY = 'body';
    const OPTION_ATTACHMENT_FILENAME = 'filename';
    const OPTION_ATTACHMENT_FILE = 'file';
    const OPTION_ATTACHMENT_MIMETYPE = 'mimetype';
    const OPTION_BCC = 'bcc';

    protected $options;

    protected $emailConstraint;

    public function __construct(
        ContextAccessor $contextAccessor,
        EmailAddressHelper $emailAddressHelper,
        EntityNameResolver $entityNameResolver,
        protected ManagerRegistry $registry,
        ValidatorInterface $validator,
        protected EmailOriginHelper $emailOriginHelper,
        protected EmailRenderer $renderer,
        protected MimeTypeGuesserInterface $mimeTypeGuesser,
        protected EmailModelSender $emailModelSender
    ) {
        parent::__construct($contextAccessor, $validator, $emailAddressHelper, $entityNameResolver);
    }

    public function initialize(array $options): self
    {
        if (isset($options[self::OPTION_BCC])) {
            $this->assertEmailAddressOption($options[self::OPTION_BCC]);
        }
        if (empty($options['from'])) {
            throw new InvalidParameterException('From parameter is required');
        }

        $this->assertEmailAddressOption($options['from']);

        if (empty($options['to'])) {
            throw new InvalidParameterException('Need to specify "to" parameters');
        }

        $this->normalizeToOption($options);

        if (empty($options['template'])) {
            throw new InvalidParameterException('Template parameter is required');
        }

        if (empty($options['entity'])) {
            throw new InvalidParameterException('Entity parameter is required');
        }

        if (isset($options[self::OPTION_ATTACHMENTS])) {
            $attachments = $options[self::OPTION_ATTACHMENTS];

            if (!is_array($attachments)) {
                throw new InvalidArgumentException('Attachments should be array');
            }
            foreach ($attachments as $attachment) {
                if (!is_array($attachment)) {
                    throw new InvalidArgumentException('Attachment options invalid');
                }

                if (!isset($attachment[self::OPTION_ATTACHMENT_BODY])
                    && !isset($attachment[self::OPTION_ATTACHMENT_FILE])
                ) {
                    throw new InvalidArgumentException(sprintf(
                        'Attachment option "%s" or "%s" should be set',
                        self::OPTION_ATTACHMENT_BODY,
                        self::OPTION_ATTACHMENT_FILE
                    ));
                }
                if (isset($attachment[self::OPTION_ATTACHMENT_BODY])
                    && isset($attachment[self::OPTION_ATTACHMENT_FILE])
                ) {
                    throw new InvalidArgumentException(sprintf(
                        'Only one of options "%s" and "%s" should be set',
                        self::OPTION_ATTACHMENT_BODY,
                        self::OPTION_ATTACHMENT_FILE
                    ));
                }
            }
        }

        $this->options = $options;
        $this->emailConstraint = new EmailConstraints(['message' => 'Invalid email address']);

        return $this;
    }

    /**
     * @param mixed $context
     * @throws EntityNotFoundException
     * @throws \Twig\Error\Error
     */
    public function executeAction($context): void
    {
        $emailModel = new Email();

        $from = $this->getEmailAddress($context, $this->options['from']);
        $this->validateAddress($from);
        $emailModel->setFrom($from);
        $to = [];

        foreach ($this->options['to'] as $email) {
            if ($email) {
                $address = $this->getEmailAddress($context, $email);
                $this->validateAddress($address);
                $to[] = $address;
            }
        }
        $emailModel->setTo($to);
        $entity = $this->contextAccessor->getValue($context, $this->options['entity']);
        $template = $this->contextAccessor->getValue($context, $this->options['template']);

        /** @var EmailTemplate $emailTemplate */
        $emailTemplate = $this
            ->registry
            ->getManagerForClass(\get_class($entity))
            ->getRepository(EmailTemplate::class)
            ->findByName($template)
        ;
        if (!$emailTemplate) {
            $errorMessage = sprintf('Template "%s" not found.', $template);
            $this->logger->error('Workflow send email action.' . $errorMessage);
            throw new EntityNotFoundException($errorMessage);
        }
        $templateData = $this->renderer->compileMessage($emailTemplate, ['entity' => $entity]);

        list ($subjectRendered, $templateRendered) = $templateData;

        $emailModel->setSubject($subjectRendered);
        $emailModel->setBody($templateRendered);
        $emailModel->setType($emailTemplate->getType());
        $emailModel->setBcc($this->getBcc($context));

        if ($entity instanceof OrganizationAwareInterface) {
            $emailModel->setOrganization($entity->getOrganization());
        } else {
            $emailModel->setOrganization($emailTemplate->getOrganization());
        }

        $this->addAttachments($emailModel, $context);

        $emailUser = null;
        try {
            $emailOrigin = $this->emailOriginHelper->getEmailOrigin(
                $emailModel->getFrom(),
                $emailModel->getOrganization()
            );

            $emailUser = $this->emailModelSender->send($emailModel, $emailOrigin);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('Workflow send email template action.', ['exception' => $exception]);
        }

        if (array_key_exists('attribute', $this->options) && $emailUser instanceof EmailUser) {
            $this->contextAccessor->setValue($context, $this->options['attribute'], $emailUser->getEmail());
        }
    }

    /**
     * @param $context
     * @return array|string
     */
    protected function getBcc($context)
    {
        if (isset($this->options[self::OPTION_BCC])) {
            $bcc = $this->getEmailAddress($context, $this->options[self::OPTION_BCC]);
            $this->validateAddress($bcc);

            $bcc = [$bcc];
        } else {
            $bcc = [];
        }

        return array_filter($bcc);
    }

    /**
     * @param Email $emailModel
     * @param $context
     */
    protected function addAttachments(Email $emailModel, $context)
    {
        if (isset($this->options[self::OPTION_ATTACHMENTS])) {
            $attachments = $this->options[self::OPTION_ATTACHMENTS];
            foreach ($attachments as $attachment) {
                $emailModel->addAttachment($this->buildAttachment($attachment, $context));
            }
        }
    }

    /**
     * @param $attachment
     * @param $context
     * @return EmailAttachment
     */
    protected function buildAttachment($attachment, $context)
    {
        if (isset($attachment[self::OPTION_ATTACHMENT_FILE])) {
            $emailAttachment = $this->buildFileAttachment($attachment, $context);
        } else {
            $emailAttachment = $this->buildStringAttachment($attachment, $context);
        }

        return $emailAttachment;
    }

    /**
     * @param $attachment
     * @param $context
     * @return EmailAttachment
     */
    protected function buildFileAttachment($attachment, $context)
    {
        $path = $this->contextAccessor->getValue($context, $attachment[self::OPTION_ATTACHMENT_FILE]);
        $content = base64_encode(file_get_contents($path));
        if (isset($attachment[self::OPTION_ATTACHMENT_MIMETYPE])) {
            $mimetype = $this->contextAccessor->getValue($context, $attachment[self::OPTION_ATTACHMENT_MIMETYPE]);
        } else {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $mimetype = $this->mimeTypeGuesser->guess($extension);
        }
        if (isset($attachment[self::OPTION_ATTACHMENT_FILENAME])) {
            $filename = $this->contextAccessor->getValue($context, $attachment[self::OPTION_ATTACHMENT_FILENAME]);
        } else {
            $filename = pathinfo($path, PATHINFO_BASENAME);
        }

        return $this->buildAttachmentFromString($content, $filename, $mimetype);
    }

    /**
     * @param $attachment
     * @param $context
     * @return EmailAttachment
     */
    protected function buildStringAttachment($attachment, $context)
    {
        $content = $this->contextAccessor->getValue($context, $attachment[self::OPTION_ATTACHMENT_BODY]);
        $filename = $this->contextAccessor->getValue($context, $attachment[self::OPTION_ATTACHMENT_FILENAME]);
        $mimetype = $this->contextAccessor->getValue($context, $attachment[self::OPTION_ATTACHMENT_MIMETYPE]);

        return $this->buildAttachmentFromString($content, $filename, $mimetype);
    }

    /**
     * @param $content
     * @param $filename
     * @param $mimetype
     * @return EmailAttachment
     */
    protected function buildAttachmentFromString($content, $filename, $mimetype)
    {
        $attachmentEntity = new AttachmentEntity();

        $attachmentContent = new EmailAttachmentContent();
        $attachmentContent->setContent($content);
        $attachmentContent->setContentTransferEncoding('base64');
        $attachmentContent->setEmailAttachment($attachmentEntity);

        $attachmentEntity->setContent($attachmentContent);
        $attachmentEntity->setContentType($mimetype);
        $attachmentEntity->setFileName($filename);

        $emailAttachment = new EmailAttachment();
        $emailAttachment->setType(EmailAttachment::TYPE_EMAIL_ATTACHMENT);
        $emailAttachment->setEmailAttachment($attachmentEntity);

        return $emailAttachment;
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

    protected function normalizeToOption(array &$options): void
    {
        if (empty($options['to'])) {
            $options['to'] = [];
        }
        if (!is_array($options['to'])
            || array_key_exists('name', $options['to'])
            || array_key_exists('email', $options['to'])
        ) {
            $options['to'] = [$options['to']];
        }

        foreach ($options['to'] as $to) {
            $this->assertEmailAddressOption($to);
        }
    }
}
