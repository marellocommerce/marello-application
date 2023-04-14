<?php

namespace Marello\Bundle\WebhookBundle\Event;

interface WebhookEventInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getLabel(): string;
}