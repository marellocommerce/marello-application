<?php

namespace Marello\Bundle\NotificationAlertBundle\Factory;

use Marello\Bundle\NotificationAlertBundle\Model\NotificationAlertContext;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertResolvedInterface;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertTypeInterface;

class NotificationAlertContextFactory
{
    public static function createError(
        string $source,
        string $message,
        ?string $solution = null,
        ?object $entity = null,
    ): NotificationAlertContext {
        return self::create(
            NotificationAlertTypeInterface::NOTIFICATION_ALERT_TYPE_ERROR,
            NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_NO,
            $source,
            $message,
            $solution,
            $entity,
        );
    }

    public static function createWarning(
        string $source,
        string $message,
        ?string $solution = null,
        ?object $entity = null,
    ): NotificationAlertContext {
        return self::create(
            NotificationAlertTypeInterface::NOTIFICATION_ALERT_TYPE_WARNING,
            NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_NO,
            $source,
            $message,
            $solution,
            $entity,
        );
    }

    public static function createSuccess(
        string $source,
        string $message,
        ?string $solution = null,
        ?object $entity = null,
    ): NotificationAlertContext {
        return self::create(
            NotificationAlertTypeInterface::NOTIFICATION_ALERT_TYPE_SUCCESS,
            NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_NA,
            $source,
            $message,
            $solution,
            $entity,
        );
    }

    public static function createInfo(
        string $source,
        string $message,
        ?string $solution = null,
        ?object $entity = null,
    ): NotificationAlertContext {
        return self::create(
            NotificationAlertTypeInterface::NOTIFICATION_ALERT_TYPE_INFO,
            NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_NA,
            $source,
            $message,
            $solution,
            $entity,
        );
    }

    protected static function create(
        string $alertType,
        string $resolved,
        string $source,
        string $message,
        ?string $solution = null,
        ?object $entity = null,
    ): NotificationAlertContext {
        $context = new NotificationAlertContext();
        $context->alertType = $alertType;
        $context->resolved = $resolved;
        $context->source = $source;
        $context->message = $message;
        if ($solution) {
            $context->solution = $solution;
        }
        if ($entity) {
            $context->relatedEntity = $entity;
        }

        return $context;
    }
}
