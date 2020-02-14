<?php

namespace Marello\Bundle\NotificationBundle\Model;

class StringAttachment implements AttachmentInterface
{
    /** @var string */
    protected $filename;

    /** @var string */
    protected $content;

    /**
     * @param string $filename
     * @param string $content
     */
    public function __construct($filename, $content = '')
    {
        $this->filename = $filename;
        $this->content = '';
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param $filename
     * @return $this
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
}
