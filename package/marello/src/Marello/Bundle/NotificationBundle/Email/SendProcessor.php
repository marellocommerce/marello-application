<?php

namespace Marello\Bundle\NotificationBundle\Email;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Marello\Bundle\LocaleBundle\Manager\EmailTemplateManager;
use Marello\Bundle\NotificationBundle\Entity\Notification;
use Marello\Bundle\NotificationBundle\Exception\MarelloNotificationException;
use Marello\Bundle\NotificationBundle\Provider\EntityNotificationConfigurationProviderInterface;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\NotificationBundle\Manager\EmailNotificationManager;

class SendProcessor
{
    /** @var EmailNotificationManager */
    protected $emailNotificationManager;

    /** @var ObjectManager */
    protected $manager;

    /** @var ActivityManager */
    protected $activityManager;

    /** @var EmailRenderer */
    protected $renderer;
    
    /** @var EmailTemplateManager  */
    protected $emailTemplateManager;

    /** @var  EntityNotificationConfigurationProviderInterface */
    protected $entityNotificationConfigurationProvider;

    /**
     * EmailSendProcessor constructor.
     *
     * @param EmailNotificationManager                         $emailNotificationManager
     * @param ObjectManager                                    $manager
     * @param ActivityManager                                  $activityManager
     * @param EmailRenderer                                    $renderer
     * @param EmailTemplateManager                             $emailTeplateManager
     * @param EntityNotificationConfigurationProviderInterface $entityNotificationConfigurationProvider
     */
    public function __construct(
        EmailNotificationManager $emailNotificationManager,
        ObjectManager $manager,
        ActivityManager $activityManager,
        EmailRenderer $renderer,
        EmailTemplateManager $emailTeplateManager,
        EntityNotificationConfigurationProviderInterface $entityNotificationConfigurationProvider
    ) {
        $this->emailNotificationManager                = $emailNotificationManager;
        $this->manager                                 = $manager;
        $this->activityManager                         = $activityManager;
        $this->renderer                                = $renderer;
        $this->emailTemplateManager                    = $emailTeplateManager;
        $this->entityNotificationConfigurationProvider = $entityNotificationConfigurationProvider;
    }

    /**
     * Send an email notification using a given template name and to given recipients.
     *
     * @param string $templateName Name of template to be sent.
     * @param array  $recipients   Array of recipient email addresses.
     * @param object $entity       Entity used to render template.
     * @param array  $data         Empty array for possible extending of additional parameters
     * @throws MarelloNotificationException
     */
    public function sendNotification($templateName, array $recipients, $entity, array $data = [])
    {
        $entityName = ClassUtils::getRealClass(get_class($entity));

        if ($this->entityNotificationConfigurationProvider->isNotificationEnabled($entityName) === false) {
            return;
        }

        $template = $this->emailTemplateManager->findTemplate($templateName, $entity);

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

        list($subjectRendered, $templateRendered) = $this->renderer->compileMessage(
            $template,
            compact('entity')
        );
        /*
         * Create new notification and process it using email notification processor.
         * Sending of notification emails is deferred, notification can be persisted but not yet sent.
         * This depends on application configuration.
         */
        $notification = new Notification($template, $recipients, $templateRendered, $entity->getOrganization());
        $this->emailNotificationManager->process($entity, [$notification]);

        $this->activityManager->addActivityTarget($notification, $entity);

        $this->manager->persist($notification);
        $this->manager->flush();
    }
}
