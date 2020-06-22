<?php

namespace Marello\Bundle\NotificationBundle\Provider;

use Marello\Bundle\NotificationBundle\Entity\Notification;
use Marello\Bundle\NotificationBundle\Model\AttachmentInterface;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Manager\File\TemporaryFile;
use Oro\Bundle\AttachmentBundle\Manager\FileManager;

class NotificationAttachmentProvider
{
    /** @var string */
    const KEY_ATTACHMENTS = 'attachments';

    /** @var FileManager */
    protected $fileManager;

    /**
     * @param FileManager $fileManager
     */
    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * Adds File entities as attachments to Notification
     *
     * @param Notification $notification
     * @param array $data
     */
    public function processNotificationAttachments(Notification $notification, array $data)
    {
        if (!isset($data[self::KEY_ATTACHMENTS]) || empty($data[self::KEY_ATTACHMENTS])) {
            return;
        }

        foreach ($data[self::KEY_ATTACHMENTS] as $attachmentData) {
            $this->processSingleAttachment($notification, $attachmentData);
        }
    }

    /**
     * Adds a File entity as attachment to Notification
     *
     * @param Notification $notification
     * @param AttachmentInterface $attachment
     */
    protected function processSingleAttachment(Notification $notification, AttachmentInterface $attachment)
    {
        $file = $this->createFileEntity($attachment);

        $notification->addAttachment($file);
    }

    /**
     * Creates File entity from AttachmentInterface object
     *
     * @param AttachmentInterface $attachmentData
     * @return File
     */
    protected function createFileEntity(AttachmentInterface $attachmentData)
    {
        $tmpFile = $this->fileManager->getTemporaryFileName($attachmentData->getFilename());
        file_put_contents($tmpFile, $attachmentData->getContent());

        $entity = new File();
        $entity->setFile(new TemporaryFile($tmpFile));
        $entity->setOriginalFilename($attachmentData->getFilename());

        return $entity;
    }
}
