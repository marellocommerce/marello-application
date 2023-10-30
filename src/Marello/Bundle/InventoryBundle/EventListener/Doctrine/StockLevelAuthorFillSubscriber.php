<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevelLogRecord;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class StockLevelAuthorFillSubscriber
 *
 * When InventoryLevel is created without user, fills user with currently logged in user if possible.
 *
 * @package Marello\Bundle\InventoryBundle\EventListener\Doctrine
 */
class StockLevelAuthorFillSubscriber implements EventSubscriber
{
    use SetsPropertyValue;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * StockLevelAuthorFillSubscriber constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Fills in stock level change user if no user is specified upon creation.
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof InventoryLevelLogRecord) {
            return;
        }

        /*
         * If stock level user is already set, do nothing.
         */
        if ($entity->getUser()) {
            return;
        }

        /*
         * Set user to currently logged in user.
         */
        $this->setPropertyValue($entity, 'user', $this->getLoggedInUser());
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    /**
     * Returns currently logged in user.
     *
     * @return User|null
     */
    protected function getLoggedInUser()
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return null;
        }

        return $token->getUser();
    }
}
