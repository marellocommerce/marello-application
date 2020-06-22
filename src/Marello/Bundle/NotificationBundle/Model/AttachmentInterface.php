<?php

namespace Marello\Bundle\NotificationBundle\Model;

interface AttachmentInterface
{
    /**
     * @return string
     */
    public function getFilename();

    /**
     * @return string
     */
    public function getContent();
}
