<?php

namespace Marello\Bundle\CoreBundle\Mailer;

use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;

use Oro\Bundle\EmailBundle\Mailer\Processor as BaseProcessor;
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
     * @param EmailModel $model
     * @param string $parentMessageId
     * @param \DateTime $messageDate
     *
     * @return \Swift_Message
     */
    protected function prepareMessage(EmailModel $model, $parentMessageId, $messageDate)
    {
        /** @var \Swift_Message $message */
        $message = $this->mailer->createMessage();
        if ($parentMessageId) {
            $message->getHeaders()->addTextHeader('References', $parentMessageId);
            $message->getHeaders()->addTextHeader('In-Reply-To', $parentMessageId);
        }
        $addresses = $this->getAddresses($model->getFrom());
        $address = $this->emailAddressHelper->extractPureEmailAddress($model->getFrom());
        $message->setDate($messageDate->getTimestamp());
        $message->setFrom($addresses);
        $message->setReplyTo($addresses);
        $message->setReturnPath($address);
        $message->setTo($this->getAddresses($model->getTo()));
        $message->setCc($this->getAddresses($model->getCc()));
        $message->setBcc($this->getAddresses($model->getBcc()));
        $message->setSubject($model->getSubject());
        $message->setBody($model->getBody(), $model->getType() === 'html' ? 'text/html' : 'text/plain');

        $this->addAttachments($message, $model);
        $this->processImages($message, $model);

        return $message;
    }

    /**
     * Process inline images. Convert it to embedded attachments and update message body.
     *
     * @param \Swift_Message  $message
     * @param EmailModel|null $model
     */
    public function processImages(\Swift_Message $message, EmailModel $model = null)
    {
        if ($model ? $model->getType() !== 'html' : $message->getContentType() !== 'text/html') {
            return;
        }

        $guesser = ExtensionGuesser::getInstance();
        $body = preg_replace_callback(
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

                        if ($model) {
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
                        }

                        return sprintf('<img%ssrc="%s"', $imgConfig, $id);
                    } else {
                        return sprintf('<img%ssrc="%s"', $imgConfig, $srcData);
                    }
                }

                return $matches[0];
            },
            $message->getBody()
        );
        $message->setBody($body);
    }
}
