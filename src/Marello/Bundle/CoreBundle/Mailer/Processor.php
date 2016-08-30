<?php

namespace Marello\Bundle\CoreBundle\Mailer;

use Oro\Bundle\EmailBundle\Mailer\Processor as BaseProcessor;


use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;

use Oro\Bundle\EmailBundle\Decoder\ContentDecoder;
use Oro\Bundle\EmailBundle\Entity\EmailAttachment;
use Oro\Bundle\EmailBundle\Entity\EmailAttachmentContent;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\EmailBundle\Event\EmailBodyAdded;
use Oro\Bundle\EmailBundle\Form\Model\Email as EmailModel;
use Oro\Bundle\EmailBundle\Form\Model\EmailAttachment as EmailAttachmentModel;
use Oro\Bundle\EmailBundle\Tools\EmailAddressHelper;

class Processor extends BaseProcessor
{

    /**
     * Process email model sending.
     *
     * @param EmailModel $model
     * @param EmailOrigin $origin Origin to send email with
     *
     * @return EmailUser
     * @throws \Swift_SwiftException
     */
    public function process(EmailModel $model, $origin = null)
    {
        $this->assertModel($model);
        $messageDate     = new \DateTime('now', new \DateTimeZone('UTC'));
        $parentMessageId = $this->getParentMessageId($model);

        /** @var \Swift_Message $message */
        $message = $this->mailer->createMessage();
        if ($parentMessageId) {
            $message->getHeaders()->addTextHeader('References', $parentMessageId);
            $message->getHeaders()->addTextHeader('In-Reply-To', $parentMessageId);
        }
        $message->setDate($messageDate->getTimestamp());
        $message->setFrom($this->getAddresses($model->getFrom()));
        $message->setTo($this->getAddresses($model->getTo()));
        $message->setCc($this->getAddresses($model->getCc()));
        $message->setBcc($this->getAddresses($model->getBcc()));
        $message->setSubject($model->getSubject());
        $message->setBody($model->getBody(), $model->getType() === 'html' ? 'text/html' : 'text/plain');

        $this->addAttachments($message, $model);
        $this->processImages($message, $model);

        $messageId = '<' . $message->generateId() . '>';

        if ($origin === null) {
            $origin = $this->getEmailOrigin($model->getFrom(), $model->getOrganization());
        }
        $this->processSend($message, $origin);

        $emailUser = $this->createEmailUser($model, $messageDate, $origin);
        $emailUser->setFolder($this->getFolder($model->getFrom(), $origin));
        $emailUser->getEmail()->setEmailBody(
            $this->emailEntityBuilder->body($message->getBody(), $model->getType() === 'html', true)
        );
        $emailUser->getEmail()->setMessageId($messageId);
        $emailUser->setSeen(true);
        if ($parentMessageId) {
            $emailUser->getEmail()->setRefs($parentMessageId);
        }

        // persist the email and all related entities such as folders, email addresses etc.
        $this->emailEntityBuilder->getBatch()->persist($this->getEntityManager());
        $this->persistAttachments($model, $emailUser->getEmail());

        // associate the email with the target entity if exist
        $contexts = $model->getContexts();
        foreach ($contexts as $context) {
            $this->emailActivityManager->addAssociation($emailUser->getEmail(), $context);
        }

        // flush all changes to the database
        $this->getEntityManager()->flush();

        $event = new EmailBodyAdded($emailUser->getEmail());
        $this->eventDispatcher->dispatch(EmailBodyAdded::NAME, $event);

        return $emailUser;
    }

    /**
     * Process inline images. Convert it to embedded attachments and update message body.
     *
     * @param \Swift_Message $message
     * @param EmailModel     $model
     */
    protected function processImages(\Swift_Message $message, EmailModel $model)
    {
        if ($model->getType() === 'html') {
            $guesser = ExtensionGuesser::getInstance();
            $body    = $message->getBody();
            $body    = preg_replace_callback(
                '/<img(.*)src(\s*)=(\s*)["\'](.*)["\']/U',
                function ($matches) use ($message, $guesser, $model) {
                    if (count($matches) === 5) {
                        // 1st match contains any data between '<img' and 'src' parts (e.g. 'width=100')
                        $imgConfig = $matches[1];

                        // 4th match contains src attribute value
                        $srcData = $matches[4];

                        if (strpos($srcData, 'data:image') === 0) {
                            list($mime, $content) = explode(';', $srcData);
                            list($encoding, $file) = explode(',', $content);
                            $mime            = str_replace('data:', '', $mime);
                            $fileName        = sprintf('%s.%s', uniqid(), $guesser->guess($mime));
                            $swiftAttachment = \Swift_Image::newInstance(
                                ContentDecoder::decode($file, $encoding),
                                $fileName,
                                $mime
                            );

                            /** @var $message \Swift_Message */
                            $id = $message->embed($swiftAttachment);

                            $attachmentContent = new EmailAttachmentContent();
                            $attachmentContent->setContent($file);
                            $attachmentContent->setContentTransferEncoding($encoding);

                            $emailAttachment = new EmailAttachment();
                            $emailAttachment->setEmbeddedContentId($swiftAttachment->getId());
                            $emailAttachment->setFileName($fileName);
                            $emailAttachment->setContentType($mime);
                            $attachmentContent->setEmailAttachment($emailAttachment);
                            $emailAttachment->setContent($attachmentContent);

                            $emailAttachmentModel = new EmailAttachmentModel();
                            $emailAttachmentModel->setEmailAttachment($emailAttachment);
                            $model->addAttachment($emailAttachmentModel);

                            return sprintf('<img%ssrc="%s"', $imgConfig, $id);
                        } else {
                            return sprintf('<img%ssrc="%s"', $imgConfig, $srcData);
                        }
                    }
                },
                $body
            );
            $message->setBody($body);
        }
    }
}
