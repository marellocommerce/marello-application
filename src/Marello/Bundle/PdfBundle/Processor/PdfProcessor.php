<?php

namespace Marello\Bundle\PdfBundle\Processor;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\ImportExportBundle\File\FileSystemOperator;
use Oro\Bundle\AttachmentBundle\Entity\Attachment;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;

use Ibnab\Bundle\PmanagerBundle\Controller\TCPDFController;
use Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate;

use Marello\Bundle\PdfBundle\Processor\EmailProcessor;


class PdfProcessor
{
    /** @var EntityManager */
    protected $manager;
    
    /** @var EmailRenderer */
    protected $renderer;

    /** @var AttachmentManager */
    protected $attachmentManager;

    /** @var FileSystemOperator */
    protected $fileSystemOperator;
    
    /** @var TCPDFController */
    protected $TCPDFController;

    /**
     * PdfProcessor constructor.
     * @param EntityManager $manager
     * @param EmailRenderer $renderer
     * @param AttachmentManager $attachmentManager
     * @param FileSystemOperator $fileSystemOperator
     * @param TCPDFController $TCPDFController
     * @param EmailProcessor $emailProcessor
     */
    public function __construct(
        EntityManager $manager,
        EmailRenderer $renderer,
        AttachmentManager $attachmentManager,
        FileSystemOperator $fileSystemOperator,
        TCPDFController $TCPDFController,
        EmailProcessor $emailProcessor
    )
    {
        $this->manager = $manager;
        $this->renderer = $renderer;
        $this->attachmentManager = $attachmentManager;
        $this->fileSystemOperator = $fileSystemOperator;
        $this->TCPDFController = $TCPDFController;
        $this->emailProcessor = $emailProcessor;
    }
    
    
    public function process($pdfTemplateName, $entity, $subject, $body, $recipients)
    {
        list ($pdfObj, $attachmentId) = $this->createPdfFile($pdfTemplateName, $entity);
        
        $this->emailProcessor->sendPdfAttached($subject, $body, $recipients, $pdfObj, $attachmentId, $entity);
    }

    public function createPdfFile($pdfTemplateName, $entity)
    {
        $templateEntity = $this->findTemplate($pdfTemplateName);
        
        if (!$templateEntity)
            return null;

        $renderedPdfBody = $this->renderer->renderWithDefaultFilters($templateEntity->getContent(), array(
            'entity' => $entity
        ));
        
        $pdfObj = $this->instancePDF($templateEntity);
        $pdfObj->AddPage();
        $pdfObj->writeHTML($renderedPdfBody, true, 0, true, 0);
        $pdfObj->lastPage();
        $fileName = $this->fileSystemOperator->generateTemporaryFileName($templateEntity->getName(), 'pdf');
        $pdfObj->Output($fileName, 'F');

        return [$pdfObj, $this->createAttachment($fileName)->getId()];
        
    }

    protected function createAttachment($fileName)
    {
        $file = $this->attachmentManager->prepareRemoteFile($fileName);
        $this->attachmentManager->upload($file);
        $this->manager->persist($file);
        $this->manager->flush();
        
        $attachment = new Attachment();
        $attachment->setFile($file);
        $this->manager->persist($attachment);
        $this->manager->flush();
        return $attachment;
    }
    
    protected function findTemplate($pdfTemplateName)
    {
        return $this->manager->getRepository('\Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate')->findOneByName($pdfTemplateName);
    }

    /**
     * @param PDFTemplate $template
     * @return \Ibnab\Bundle\PmanagerBundle\Controller\TCPDF
     */
    protected function instancePDF(PDFTemplate $template)
    {
        $orientation = $template->getOrientation() ? $template->getOrientation() : 'P';
        $unit = $template->getUnit() ? $template->getUnit() : 'mm';
        $format = $template->getFormat() ? $template->getFormat() : 'A4';
        $right = $template->getMarginright() ? $template->getMarginright() : '2';
        $top = $template->getMargintop() ? $template->getMargintop() : '2';
        $left = $template->getMarginleft() ? $template->getMarginleft() : '2';
        $bottom = $template->getMarginBottom() ? $template->getMarginBottom() : '2';
        if($template->getAutobreak() == 1)
        {
            $autobreak= true;
        }
        else
        {
            $autobreak= false;
        }
        $pdfObj = $this->TCPDFController->create($orientation,$unit,$format, true, 'UTF-8', false);

        $pdfObj->SetCreator($template->getAuteur());
        $pdfObj->SetAuthor($template->getAuteur());
        $pdfObj->SetMargins($left, $top, $right);
        $pdfObj->SetAutoPageBreak($autobreak, $bottom);
        return $pdfObj;
    }
    
}
