<?php

namespace Marello\Bundle\NotificationBundle\Email;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\LocaleBundle\Manager\EmailTemplateManager;
use Marello\Bundle\NotificationBundle\Entity\Notification;
use Marello\Bundle\NotificationBundle\Exception\MarelloNotificationException;
use Marello\Bundle\NotificationBundle\Provider\EntityNotificationConfigurationProviderInterface;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Model\EmailTemplate as EmailTemplateModel;
use Oro\Bundle\EmailBundle\Model\EmailTemplateInterface;
use Oro\Bundle\NotificationBundle\Manager\EmailNotificationManager;
use Oro\Bundle\NotificationBundle\Manager\EmailNotificationSender;
use Oro\Bundle\NotificationBundle\Model\TemplateEmailNotification;
use Oro\Bundle\NotificationBundle\Model\TemplateEmailNotificationInterface;

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

    /** @var EmailNotificationSender $emailNotificationSender */
    protected $emailNotificationSender;

    /** @var bool $saveNotificationAsActivity */
    protected $saveNotificationAsActivity = true;

    /**
     * EmailSendProcessor constructor.
     *
     * @param EmailNotificationManager                         $emailNotificationManager
     * @param ObjectManager                                    $manager
     * @param ActivityManager                                  $activityManager
     * @param EmailRenderer                                    $renderer
     * @param EmailTemplateManager                             $emailTemplateManager
     * @param EntityNotificationConfigurationProviderInterface $entityNotificationConfigurationProvider
     */
    public function __construct(
        EmailNotificationManager $emailNotificationManager,
        ObjectManager $manager,
        ActivityManager $activityManager,
        EmailRenderer $renderer,
        EmailTemplateManager $emailTemplateManager,
        EntityNotificationConfigurationProviderInterface $entityNotificationConfigurationProvider
    ) {
        $this->emailNotificationManager                = $emailNotificationManager;
        $this->manager                                 = $manager;
        $this->activityManager                         = $activityManager;
        $this->renderer                                = $renderer;
        $this->emailTemplateManager                    = $emailTemplateManager;
        $this->entityNotificationConfigurationProvider = $entityNotificationConfigurationProvider;
    }

    /**
     * @param EmailNotificationSender $emailNotificationSender
     */
    public function setEmailNotificationSender(EmailNotificationSender $emailNotificationSender)
    {
        $this->emailNotificationSender = $emailNotificationSender;
    }

    /**
     * Send an email notification using a given template name and to given recipients.
     *
     * @param string $templateName Name of template to be sent.
     * @param array  $recipients   Array of recipient email addresses.
     * @param object $entity       Entity used to render template.
     * @param array  $data         Empty array for possible extending of additional parameters
     * @throws MarelloNotificationException
     * @throws \Oro\Bundle\NotificationBundle\Exception\NotificationSendException
     * @throws \Twig_Error
     */
    public function sendNotification($templateName, array $recipients, $entity, array $data = [])
    {
        $entityName = $this->getRealClassName($entity);

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
        try {
            // send compiled notification
            $this->emailNotificationSender->send(
                $this->getNotification($templateName, $recipients, $entity),
                $this->createEmailModel($template, $templateRendered, $subjectRendered)
            );
        } catch (\Exception $e) {
            $this->emailNotificationManager->processSingle(
                $this->getNotification($templateName, $recipients, $entity),
                [$notification]
            );
        }
        if ($this->saveNotificationAsActivity) {
            $this->activityManager->addActivityTarget($notification, $entity);

            $this->manager->persist($notification);
            $this->manager->flush();
        }
    }

    /**
     * {@inheritdoc}
     * @param bool $saveAsActivity
     */
    public function setNotifcationShouldBeSavedAsActivity(bool $saveAsActivity)
    {
        $this->saveNotificationAsActivity = $saveAsActivity;
    }

    /**
     * @param string $templateName
     * @param array $recipients
     * @param object $entity
     * @return TemplateEmailNotificationInterface
     */
    protected function getNotification($templateName, array $recipients, $entity)
    {
        return new TemplateEmailNotification(
            new EmailTemplateCriteria(
                $templateName,
                $this->getRealClassName($entity)
            ),
            $recipients,
            $entity
        );
    }

    /**
     * @param object $entity
     * @return string
     */
    protected function getRealClassName($entity)
    {
        return $this->manager->getClassMetadata(get_class($entity))->getName();
    }

    /**
     * @param $template
     * @param $renderedContent
     * @param $renderedSubject
     * @return EmailTemplateModel
     */
    private function createEmailModel($template, $renderedContent, $renderedSubject)
    {
        $emailTemplateModel = new EmailTemplateModel();
        $emailTemplateModel
            ->setSubject($renderedSubject)
            ->setContent($renderedContent)
            ->setType($this->getTemplateContentType($template));

        return $emailTemplateModel;
    }

    /**
     * @param EmailTemplateInterface $emailTemplate
     * @return string
     */
    private function getTemplateContentType(EmailTemplateInterface $emailTemplate)
    {
        return $emailTemplate->getType() === EmailTemplate::TYPE_HTML
            ? EmailTemplateModel::CONTENT_TYPE_HTML
            : EmailTemplateModel::CONTENT_TYPE_TEXT;
    }
}
