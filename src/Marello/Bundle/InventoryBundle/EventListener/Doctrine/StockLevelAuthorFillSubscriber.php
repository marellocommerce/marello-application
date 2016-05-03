<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

use Marello\Bundle\InventoryBundle\Entity\StockLevel;

use Oro\Bundle\UserBundle\Entity\User;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class StockLevelAuthorFillSubscriber
 *
 * When StockLevel is created without author, fills author with currently logged in user if possible.
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
     * Fills in stock level change author if no author is specified upon creation.
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof StockLevel) {
            return;
        }

        /*
         * If stock level author is already set, do nothing.
         */
        if ($entity->getAuthor()) {
            return;
        }

        /*
         * Set author to currently logged in user.
         */
        $this->setPropertyValue($entity, 'author', $this->getLoggedInUser());
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
