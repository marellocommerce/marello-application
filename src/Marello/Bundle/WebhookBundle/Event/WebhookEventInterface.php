<?php

namespace Marello\Bundle\WebhookBundle\Event;

use Marello\Bundle\WebhookBundle\Model\WebhookContext;

interface WebhookEventInterface
{
    public static function getName(): string;

    public static function getLabel(): string;

    public function getContext(): WebhookContext;
}
