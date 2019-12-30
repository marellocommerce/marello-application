<?php

namespace Marello\Bundle\PdfBundle\Workflow\Action;

use Doctrine\ORM\EntityNotFoundException;
use Oro\Bundle\EmailBundle\Entity\EmailAttachmentContent;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\EmailBundle\Entity\EmailAttachment as AttachmentEntity;
use Oro\Bundle\EmailBundle\Form\Model\Email;
use Oro\Bundle\EmailBundle\Form\Model\EmailAttachment;
use Oro\Bundle\EmailBundle\Workflow\Action\SendEmailTemplate;
use Oro\Component\Action\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class SendEmailTemplateAttachmentAction extends SendEmailTemplate
{
    const OPTION_ATTACHMENTS = 'attachments';
    const OPTION_ATTACHMENT_BODY = 'body';
    const OPTION_ATTACHMENT_FILENAME = 'filename';
    const OPTION_ATTACHMENT_FILE = 'file';
    const OPTION_ATTACHMENT_MIMETYPE = 'mimetype';
    const OPTION_BCC = 'bcc';

    private $options;

    protected $mime_type_guesser;

    public function initialize(array $options): SendEmailTemplate
    {
        if (isset($options[self::OPTION_BCC])) {
            $this->assertEmailAddressOption($options[self::OPTION_BCC]);
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
        return parent::initialize($options);
    }

    public function executeAction($context): void
    {
        $emailModel = $this->getEmailModel();

        $from = $this->getEmailAddress($context, $this->options['from']);
        $this->validateAddress($from);
        $emailModel->setFrom($from);
        $to = [];
        foreach ($this->options['to'] as $email) {
            if ($email) {
                $address = $this->getEmailAddress($context, $email);
                $this->validateAddress($address);
                $to[] = $this->getEmailAddress($context, $address);
            }
        }
        $emailModel->setTo($to);
        $entity = $this->contextAccessor->getValue($context, $this->options['entity']);
        $template = $this->contextAccessor->getValue($context, $this->options['template']);

        $emailTemplate = $this->objectManager
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

        $this->addAttachments($emailModel, $context);

        $emailUser = null;
        try {
            $emailUser = $this->emailProcessor->process(
                $emailModel,
                $this->emailProcessor->getEmailOrigin($emailModel->getFrom(), $emailModel->getOrganization())
            );
        } catch (\Swift_SwiftException $exception) {
            $this->logger->error('Workflow send email template action.', ['exception' => $exception]);
        }

        if (array_key_exists('attribute', $this->options) && $emailUser instanceof EmailUser) {
            $this->contextAccessor->setValue($context, $this->options['attribute'], $emailUser->getEmail());
        }
    }

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

    protected function addAttachments(Email $emailModel, $context)
    {
        if (isset($this->options[self::OPTION_ATTACHMENTS])) {
            $attachments = $this->options[self::OPTION_ATTACHMENTS];
            foreach ($attachments as $attachment) {
                $emailModel->addAttachment($this->buildAttachment($attachment, $context));
            }
        }
    }

    protected function buildAttachment($attachment, $context)
    {
        if (isset($attachment[self::OPTION_ATTACHMENT_FILE])) {
            $emailAttachment = $this->buildFileAttachment($attachment, $context);
        } else {
            $emailAttachment = $this->buildStringAttachment($attachment, $context);
        }

        return $emailAttachment;
    }

    protected function buildFileAttachment($attachment, $context)
    {
        $path = $this->contextAccessor->getValue($context, $attachment[self::OPTION_ATTACHMENT_FILE]);
        $content = base64_encode(file_get_contents($path));
        if (isset($attachment[self::OPTION_ATTACHMENT_MIMETYPE])) {
            $mimetype = $this->contextAccessor->getValue($context, $attachment[self::OPTION_ATTACHMENT_MIMETYPE]);
        } else {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $mimetype = $this->getMimeTypeGuesser()->guess($extension);
        }
        if (isset($attachment[self::OPTION_ATTACHMENT_FILENAME])) {
            $filename = $this->contextAccessor->getValue($context, $attachment[self::OPTION_ATTACHMENT_FILENAME]);
        } else {
            $filename = pathinfo($path, PATHINFO_BASENAME);
        }

        return $this->buildAttachmentFromString($content, $filename, $mimetype);
    }

    protected function buildStringAttachment($attachment, $context)
    {
        $content = $this->contextAccessor->getValue($context, $attachment[self::OPTION_ATTACHMENT_BODY]);
        $filename = $this->contextAccessor->getValue($context, $attachment[self::OPTION_ATTACHMENT_FILENAME]);
        $mimetype = $this->contextAccessor->getValue($context, $attachment[self::OPTION_ATTACHMENT_MIMETYPE]);

        return $this->buildAttachmentFromString($content, $filename, $mimetype);
    }

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

    protected function getMimeTypeGuesser()
    {
        if ($this->mime_type_guesser === null) {
            $this->mime_type_guesser = MimeTypeGuesser::getInstance();
        }

        return $this->mime_type_guesser;
    }
}
