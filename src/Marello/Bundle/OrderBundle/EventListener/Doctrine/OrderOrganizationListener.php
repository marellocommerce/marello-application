<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Marello\Bundle\OrderBundle\Entity\Order;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OrderOrganizationListener
{
    public function __construct(
        protected TokenStorageInterface $tokenStorage
    ) {
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$entity instanceof Order) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            throw new \Exception('An order must be created by user.');
        }

        $user = $token->getUser();
        $organization = $user->getOrganization();
        if ($entity->getOrganization() === null) {
            $entity->setOrganization($organization);
        }
        if ($entity->getOwner() === null) {
            $entity->setOwner($user);
        }

        foreach ($entity->getItems() as $item) {
            if ($item->getOrganization() === null) {
                $item->setOrganization($organization);
            }

            if ($item->getOwner() === null) {
                $entity->setOwner($user);
            }
        }
    }
}
