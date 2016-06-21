<?php

namespace Marello\Bundle\NotificationBundle\Email;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Marello\Bundle\NotificationBundle\Entity\Notification;
use Marello\Bundle\NotificationBundle\Exception\MarelloNotificationException;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\NotificationBundle\Processor\EmailNotificationProcessor;

use Oro\Bundle\EmailBundle\Entity\EmailBody;
use Oro\Bundle\EmailBundle\Tools\EmailAttachmentTransformer;
use Oro\Bundle\AttachmentBundle\Entity\Attachment;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;
use Oro\Bundle\ImportExportBundle\File\FileSystemOperator;
use Oro\Bundle\EmailBundle\Mailer\Processor;
use Oro\Bundle\EmailBundle\Builder\EmailModelBuilder;
use Ibnab\Bundle\PmanagerBundle\Controller\TCPDFController;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Oro\Bundle\EmailBundle\Form\Model\EmailAttachment as ModelEmailAttachment;
use Oro\Bundle\EmailBundle\Entity\EmailAttachmentContent;
use Oro\Bundle\EmailBundle\Entity\EmailAttachment;

use Swift_Mailer;
use Swift_Message;

class SendProcessor
{
    /** @var EmailNotificationProcessor */
    protected $emailNotificationProcessor;

    /** @var ObjectManager */
    protected $manager;

    /** @var ActivityManager */
    protected $activityManager;

    /** @var EmailRenderer */
    protected $renderer;

    /** @var AttachmentManager */
    protected $attachmentManager;

    /** @var FileSystemOperator */
    protected $fileSystemOperator;

    /** @var TCPDFController */
    protected $TCPDFController;

    /** @var Router */
    protected $router;

    /** @var EmailModelBuilder */
    protected $emailModelBuilder;

    /** @var Processor */
    protected $processor;

    /** @var EmailAttachmentTransformer */
    protected $emailAttachmentTransformer;

    protected $mailer;

    /**
     * EmailSendProcessor constructor.
     *
     * @param EmailNotificationProcessor $emailNotificationProcessor
     * @param ObjectManager $manager
     * @param ActivityManager $activityManager
     * @param EmailRenderer $renderer
     * @param AttachmentManager $attachmentManager
     */
    public function __construct(
        EmailNotificationProcessor $emailNotificationProcessor,
        ObjectManager $manager,
        ActivityManager $activityManager,
        EmailRenderer $renderer,
        AttachmentManager $attachmentManager,
        FileSystemOperator $fileSystemOperator,
        TCPDFController $TCPDFController,
        Router $router,
        EmailModelBuilder $emailModelBuilder,
        Processor $processor,
        EmailAttachmentTransformer $emailAttachmentTransformer,
        \Swift_Mailer $mailer
    ) {
        $this->emailNotificationProcessor = $emailNotificationProcessor;
        $this->manager                    = $manager;
        $this->activityManager            = $activityManager;
        $this->renderer = $renderer;
        $this->attachmentManager = $attachmentManager;
        $this->fileSystemOperator = $fileSystemOperator;
        $this->TCPDFController = $TCPDFController;
        $this->router = $router;
        $this->emailModelBuilder = $emailModelBuilder;
        $this->processor = $processor;
        $this->emailAttachmentTransformer = $emailAttachmentTransformer;
        $this->mailer = $mailer;
    }

    /**
     * Send an email notification using a given template name and to given recipients.
     *
     * @param string $templateName Name of template to be sent.
     * @param array  $recipients   Array of recipient email addresses.
     * @param object $entity       Entity used to render template.
     *
     * @throws MarelloNotificationException
     */
    public function sendNotification($templateName, array $recipients, $entity, $pdfTemplateName)
    {
        $entityName = ClassUtils::getRealClass(get_class($entity));

        $template = $this->manager
            ->getRepository(EmailTemplate::class)
            ->findOneBy(['name' => $templateName, 'entityName' => $entityName]);

        /*
         * If template is not found, throw an exception.
         */
        if ($template === null) {
            throw new MarelloNotificationException(
                sprintf(
                    'Email template with name "%s" for entity "%s" was not found. Check if such template exists.',
                    $templateName,
                    $entityName
                )
            );
        }

        list ($subjectRendered, $templateRendered) = $this->renderer->compileMessage(
            $template,
            compact('entity')
        );
        /*
         * Create new notification and process it using email notification processor.
         * Sending of notification emails is deferred, notification can be persisted but not yet sent.
         * This depends on application configuration.
         */
        if ($pdfTemplateName) {
            $pdfData = $this->createPdfFile($pdfTemplateName, $entity, $entityName);
            $this->sendPdf($subjectRendered, $templateRendered, $pdfData, $recipients, $entity);
//            $this->swift($template, $pdfData, $recipients);
        }
        else {
            $notification = new Notification($template, $recipients, $templateRendered, $entity->getOrganization());
            $this->emailNotificationProcessor->process($entity, [$notification]);

            $this->activityManager->addActivityTarget($notification, $entity);

            $this->manager->persist($notification);
            $this->manager->flush();
        }

    }

    private function createPdfFile($pdfTemplateName, $entity, $entityName)
    {
        $responseData = array();
        
        $pdfTemplateEntity = $this->manager->getRepository('\Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate')->findOneByName($pdfTemplateName);

        $pdfObj = $this->instancePDF($pdfTemplateEntity);
        $pdfObj->AddPage();
        $renderedPdf = $this->renderer->renderWithDefaultFilters($pdfTemplateEntity->getContent(),array(
            'entity' => $entity
        ));

        $renderedPdf = $pdfTemplateEntity->getCss(). $renderedPdf;
        $pdfObj->writeHTML($renderedPdf, true, 0, true, 0);
        $pdfObj->lastPage();

        $fileName = $this->fileSystemOperator->generateTemporaryFileName($entityName, 'pdf');
        $pdfObj->Output($fileName, 'F');
        
        $url = $this->router->generate('oro_importexport_export_download', array('fileName' => basename($fileName)));
        
        $file = $this->attachmentManager->prepareRemoteFile($fileName);
        $this->attachmentManager->upload($file);
        $this->manager->persist($file);
        $this->manager->flush();
//        $this->manager->getConnection()->commit();

        $attachment = new Attachment();
        $attachment->setFile($file);
        $this->manager->persist($attachment);
        $this->manager->flush();
//        $this->manager->getConnection()->commit();
        $responseData['renderedPdf'] = $renderedPdf;
        $responseData['attachmentId'] = $attachment->getId();
        $responseData['file'] = $file;
        $responseData['pdfObj'] = $pdfObj;

        return $responseData;
    }

    protected function instancePDF($templateResult)
    {
        $orientation = $templateResult->getOrientation() ? $templateResult->getOrientation() : 'P';
        $unit = $templateResult->getUnit() ? $templateResult->getUnit() : 'mm';
        $format = $templateResult->getFormat() ? $templateResult->getFormat() : 'A4';
        $right = $templateResult->getMarginright() ? $templateResult->getMarginright() : '2';
        $top = $templateResult->getMargintop() ? $templateResult->getMargintop() : '2';
        $left = $templateResult->getMarginleft() ? $templateResult->getMarginleft() : '2';
        $bottom = $templateResult->getMarginBottom() ? $templateResult->getMarginBottom() : '2';
        if($templateResult->getAutobreak() == 1)
        {
            $autobreak= true;
        }
        else
        {
            $autobreak= false;
        }
        $pdfObj = $this->TCPDFController->create($orientation,$unit,$format, true, 'UTF-8', false);

        $pdfObj->SetCreator($templateResult->getAuteur());
        $pdfObj->SetAuthor($templateResult->getAuteur());
        $pdfObj->SetMargins($left, $top, $right);
        $pdfObj->SetAutoPageBreak($autobreak, $bottom);
        return $pdfObj;
    }
    
    protected function sendPdf($subjectRendered, $emailRendered, $pdfData, $recipients, $entity)
    {
        //parse parameters
        $attachmentId = $pdfData['attachmentId'];
        $attachment = $this->manager->getRepository('OroAttachmentBundle:Attachment')->find($attachmentId);

        //email model: Oro\Bundle\EmailBundle\Form\Model\Email
        $emailModel = $this->emailModelBuilder->createEmailModel();
        $emailModel->setContexts(array($entity));

        //email attachment entity: Oro\Bundle\EmailBundle\Entity\EmailAttachment
        $emailAttachment = new EmailAttachment();

        //email body: Oro\Bundle\EmailBundle\Entity\EmailBody
        $emailBody = new EmailBody();
        $emailBody->setHasAttachments(true)->setBodyContent("hellobodycontent");

        $emailAttachment->setFile($attachment->getFile());
        $emailAttachment->setFileName($attachment->getFile()->getFileName());

        $emailAttachmentContent = new EmailAttachmentContent();
        $pdfObj = $pdfData['pdfObj'];
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
        $emailModel->setSubject($subjectRendered);
        $emailModel->setBody($emailRendered);
        $this->processor->process($emailModel);
    }

    private function swift($emailData, $pdfData, $recipients)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Hello Email')
            ->setFrom(array('joey@madia.nl' => 'joey'))
            ->setTo('joey@madia.nl')
            ->setBody('lalalala')
            ->attach(\Swift_Attachment::fromPath(APP_PATH. "/temp/5768034b7ef8b.pdf" ))
        ;

        $this->mailer->send($message);
    }

}
