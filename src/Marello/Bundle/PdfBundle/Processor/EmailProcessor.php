<?php

namespace Marello\Bundle\PdfBundle\Processor;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\EmailBundle\Builder\EmailModelBuilder;
use Oro\Bundle\EmailBundle\Tools\EmailAttachmentTransformer;
use Oro\Bundle\EmailBundle\Mailer\Processor;

use Oro\Bundle\EmailBundle\Entity\EmailBody;
use Oro\Bundle\EmailBundle\Form\Model\EmailAttachment as ModelEmailAttachment;
use Oro\Bundle\EmailBundle\Entity\EmailAttachmentContent;
use Oro\Bundle\EmailBundle\Entity\EmailAttachment;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class EmailProcessor
{
    /** @var EntityManager */
    protected $manager;
    
    /** @var EmailModelBuilder */
    protected $emailModelBuilder;

    /** @var Processor */
    protected $processor;

    /** @var EmailAttachmentTransformer */
    protected $emailAttachmentTransformer;

    /**
     * EmailProcessor constructor.
     * @param EntityManager $manager
     * @param EmailModelBuilder $emailModelBuilder
     * @param Processor $processor
     * @param EmailAttachmentTransformer $emailAttachmentTransformer
     */
    public function __construct(
        EntityManager $manager,
        EmailModelBuilder $emailModelBuilder,
        Processor $processor,
        EmailAttachmentTransformer $emailAttachmentTransformer
    ) {
        $this->manager = $manager;
        $this->emailModelBuilder = $emailModelBuilder;
        $this->processor = $processor;
        $this->emailAttachmentTransformer = $emailAttachmentTransformer;
    }

    /**
     * Creates all the structure of email and sends with pdf attached 
     * 
     * @param $subject
     * @param $body
     * @param $recipients
     * @param $pdfObj
     * @param $attachmentId
     * @param $entity
     * @return null
     */
    public function sendPdfAttached($subject, $body, $recipients, $pdfObj, $attachmentId, $entity)
    {
        $attachment = $this->findAttachment($attachmentId);

        // Oro\Bundle\EmailBundle\Form\Model\Email
        $emailModel = $this->emailModelBuilder->createEmailModel();
        $emailModel->setContexts(array($entity));

        // Oro\Bundle\EmailBundle\Entity\EmailAttachment
        $emailAttachment = new EmailAttachment();

        // Oro\Bundle\EmailBundle\Entity\EmailBody
        $emailBody = new EmailBody();
        $emailBody->setHasAttachments(true);

        $emailAttachment->setFile($attachment->getFile());
        $emailAttachment->setFileName($attachment->getFile()->getFileName());

        $emailAttachmentContent = new EmailAttachmentContent();
        $emailAttachmentContent->setContent(base64_encode($pdfObj->getPDFData()));
        $emailAttachmentContent->setContentTransferEncoding('base64');

        $emailAttachment->setContentType($attachment->getFile()->getMimeType());
        $emailAttachment->setContent($emailAttachmentContent);
        $emailAttachment->setEmailBody($emailBody);

        $modelEmailAttachment = $this->emailAttachmentTransformer->entityToModel($emailAttachment);

        $modelEmailAttachment->setType(ModelEmailAttachment::TYPE_ATTACHMENT);
        $modelEmailAttachment->setFileSize($attachment->getFile()->getFileSize());
        $modelEmailAttachment->setModified($attachment->getFile()->getUpdatedAt());
        $modelEmailAttachment->setId($attachment->getId());
        $modelEmailAttachment->setEmailAttachment($emailAttachment);
        
        $emailModel->addAttachment($modelEmailAttachment);
        $emailModel->setTo($recipients);
        $emailModel->setSubject($subject);
        $emailModel->setBody($body);
        
        $this->processor->process($emailModel);
    }


    /**
     * @param $attachmentId
     * @return null|object
     * @throws NotFoundHttpException
     */
    protected function findAttachment($attachmentId)
    {
        $attachment =  $this->manager->getRepository('OroAttachmentBundle:Attachment')->find($attachmentId);

        if (!$attachment) {
            throw new NotFoundHttpException('Attachment with id '. $attachmentId. ' not found');
        }

        return $attachment;
    }

}