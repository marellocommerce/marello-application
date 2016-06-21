<?php

namespace Marello\Bundle\NotificationBundle\Email;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Marello\Bundle\NotificationBundle\Entity\Notification;
use Marello\Bundle\NotificationBundle\Exception\MarelloNotificationException;
use Marello\Bundle\PdfBundle\Processor\PdfProcessor;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\NotificationBundle\Processor\EmailNotificationProcessor;

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

    /** @var PdfProcessor  */
    protected $pdfProcessor;

    /**
     * EmailSendProcessor constructor.
     *
     * @param EmailNotificationProcessor $emailNotificationProcessor
     * @param ObjectManager $manager
     * @param ActivityManager $activityManager
     * @param EmailRenderer $renderer
     * @param PdfProcessor $pdfProcessor
     */
    public function __construct(
        EmailNotificationProcessor $emailNotificationProcessor,
        ObjectManager $manager,
        ActivityManager $activityManager,
        EmailRenderer $renderer,
        PdfProcessor $pdfProcessor
    ) {
        $this->emailNotificationProcessor = $emailNotificationProcessor;
        $this->manager                    = $manager;
        $this->activityManager            = $activityManager;
        $this->renderer = $renderer;
        $this->pdfProcessor = $pdfProcessor;
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
            $this->pdfProcessor->sendPdfAttached($pdfTemplateName, $entity, $subjectRendered, $templateRendered, $recipients);
        }
        else {
            $notification = new Notification($template, $recipients, $templateRendered, $entity->getOrganization());
            $this->emailNotificationProcessor->process($entity, [$notification]);

            $this->activityManager->addActivityTarget($notification, $entity);

            $this->manager->persist($notification);
            $this->manager->flush();
        }

    }
}
