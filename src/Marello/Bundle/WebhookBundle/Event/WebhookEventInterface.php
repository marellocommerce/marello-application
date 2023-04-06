<?php

namespace Marello\Bundle\WebhookBundle\Event;

interface WebhookEventInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getLabel();
}