<?php

namespace Marello\Bundle\NotificationAlertBundle\Model;

class NotificationAlertContext
{
    public string $alertType;

    public string $resolved;

    public string $source;

    public string $message;

    public ?string $solution = null;

    public ?object $relatedEntity = null;
}
