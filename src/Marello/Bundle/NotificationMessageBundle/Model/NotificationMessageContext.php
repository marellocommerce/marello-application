<?php

namespace Marello\Bundle\NotificationMessageBundle\Model;

use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

class NotificationMessageContext
{
    public string $alertType;

    public string $resolved;

    public string $source;

    public string $title;

    public string $message;

    public bool $flush;

    public ?string $solution = null;

    public ?object $relatedEntity = null;

    public ?string $operation = null;

    public ?string $step = null;

    public ?string $externalId = null;

    public ?string $log = null;

    public bool $queue;

    public ?OrganizationInterface $organization = null;
}
