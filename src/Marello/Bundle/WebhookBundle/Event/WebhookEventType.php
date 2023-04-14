<?php

namespace Marello\Bundle\WebhookBundle\Event;

use Symfony\Component\HttpFoundation\ParameterBag;

class WebhookEventType extends ParameterBag implements WebhookEventInterface
{
    public const NAME_FIELD = 'name';
    public const LABEL_FIELD = 'label';

    public function getName(): string
    {
        return $this->get(self::NAME_FIELD);
    }

    public function getLabel(): string
    {
        return $this->get(self::LABEL_FIELD);
    }
}
