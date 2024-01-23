<?php

namespace Marello\Bundle\NotificationMessageBundle\Factory;

use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

use Marello\Bundle\NotificationMessageBundle\Model\NotificationMessageContext;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageTypeInterface;

class NotificationMessageContextFactory
{
    /**
     * Create Error Notification Message
     * @param string $source
     * @param string $title
     * @param string $message
     * @param string|null $solution
     * @param object|null $entity
     * @param string|null $operation
     * @param string|null $step
     * @param string|null $externalId
     * @param string|null $log
     * @param OrganizationInterface|null $organization
     * @param bool $flush
     * @return NotificationMessageContext
     */
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
        bool $queue = false
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
            $flush,
            $queue
        );
    }

    /**
     * Create Warning Notification Message
     * @param string $source
     * @param string $title
     * @param string $message
     * @param string|null $solution
     * @param object|null $entity
     * @param string|null $operation
     * @param string|null $step
     * @param string|null $externalId
     * @param string|null $log
     * @param OrganizationInterface|null $organization
     * @param bool $flush
     * @return NotificationMessageContext
     */
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
        bool $queue = false
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
            $flush,
            $queue
        );
    }

    /**
     * Create Success Notification Message
     * @param string $source
     * @param string $title
     * @param string $message
     * @param string|null $solution
     * @param object|null $entity
     * @param string|null $operation
     * @param string|null $step
     * @param string|null $externalId
     * @param string|null $log
     * @param OrganizationInterface|null $organization
     * @param bool $flush
     * @return NotificationMessageContext
     */
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
        bool $queue = false
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
            $flush,
            $queue
        );
    }

    /**
     * Create Info Notification Message
     * @param string $source
     * @param string $title
     * @param string $message
     * @param string|null $solution
     * @param object|null $entity
     * @param string|null $operation
     * @param string|null $step
     * @param string|null $externalId
     * @param string|null $log
     * @param OrganizationInterface|null $organization
     * @param bool $flush
     * @return NotificationMessageContext
     */
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
        bool $queue = false
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
            $flush,
            $queue
        );
    }

    /**
     * Create generic Notification Message
     * @param string $alertType
     * @param string $resolved
     * @param string $source
     * @param string $title
     * @param string $message
     * @param string|null $solution
     * @param object|null $entity
     * @param string|null $operation
     * @param string|null $step
     * @param string|null $externalId
     * @param string|null $log
     * @param OrganizationInterface|null $organization
     * @param bool $flush
     * @return NotificationMessageContext
     */
    public static function create(
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
        bool $queue = false
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
        $context->queue = $queue;

        return $context;
    }
}
