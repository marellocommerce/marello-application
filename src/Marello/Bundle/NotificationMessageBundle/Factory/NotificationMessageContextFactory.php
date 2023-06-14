<?php

namespace Marello\Bundle\NotificationMessageBundle\Factory;

use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

use Marello\Bundle\NotificationMessageBundle\Model\NotificationMessageContext;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageTypeInterface;

class NotificationMessageContextFactory
{
    public static function createError(
        string $source,
        string $title,
        string $message,
        ?string $solution = null,
        ?object $entity = null,
        ?string $operation = null,
        ?string $step = null,
        ?string $externalId = null,
        ?string $log = null,
        ?OrganizationInterface $organization = null,
        bool $flush = true,
    ): NotificationMessageContext {
        return self::create(
            NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_ERROR,
            NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_NO,
            $source,
            $title,
            $message,
            $solution,
            $entity,
            $operation,
            $step,
            $externalId,
            $log,
            $organization,
            $flush
        );
    }

    public static function createWarning(
        string $source,
        string $title,
        string $message,
        ?string $solution = null,
        ?object $entity = null,
        ?string $operation = null,
        ?string $step = null,
        ?string $externalId = null,
        ?string $log = null,
        ?OrganizationInterface $organization = null,
        bool $flush = true,
    ): NotificationMessageContext {
        return self::create(
            NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_WARNING,
            NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_NO,
            $source,
            $title,
            $message,
            $solution,
            $entity,
            $operation,
            $step,
            $externalId,
            $log,
            $organization,
            $flush
        );
    }

    public static function createSuccess(
        string $source,
        string $title,
        string $message,
        ?string $solution = null,
        ?object $entity = null,
        ?string $operation = null,
        ?string $step = null,
        ?string $externalId = null,
        ?string $log = null,
        ?OrganizationInterface $organization = null,
        bool $flush = true,
    ): NotificationMessageContext {
        return self::create(
            NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_SUCCESS,
            NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_NA,
            $source,
            $title,
            $message,
            $solution,
            $entity,
            $operation,
            $step,
            $externalId,
            $log,
            $organization,
            $flush
        );
    }

    public static function createInfo(
        string $source,
        string $title,
        string $message,
        ?string $solution = null,
        ?object $entity = null,
        ?string $operation = null,
        ?string $step = null,
        ?string $externalId = null,
        ?string $log = null,
        ?OrganizationInterface $organization = null,
        bool $flush = true,
    ): NotificationMessageContext {
        return self::create(
            NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_INFO,
            NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_NA,
            $source,
            $title,
            $message,
            $solution,
            $entity,
            $operation,
            $step,
            $externalId,
            $log,
            $organization,
            $flush
        );
    }

    protected static function create(
        string $alertType,
        string $resolved,
        string $source,
        string $title,
        string $message,
        ?string $solution = null,
        ?object $entity = null,
        ?string $operation = null,
        ?string $step = null,
        ?string $externalId = null,
        ?string $log = null,
        ?OrganizationInterface $organization = null,
        bool $flush = true,
    ): NotificationMessageContext {
        $context = new NotificationMessageContext();
        $context->alertType = $alertType;
        $context->resolved = $resolved;
        $context->source = $source;
        $context->title = $title;
        $context->message = $message;
        if ($solution) {
            $context->solution = $solution;
        }
        if ($entity) {
            $context->relatedEntity = $entity;
        }
        if ($operation) {
            $context->operation = $operation;
        }
        if ($step) {
            $context->step = $step;
        }
        if ($externalId) {
            $context->externalId = $externalId;
        }
        if ($log) {
            $context->log = $log;
        }
        if ($organization) {
            $context->organization = $organization;
        }

        $context->flush = $flush;

        return $context;
    }
}
