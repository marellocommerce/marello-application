<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\OrderBundle\Entity\Order;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OrderOrganizationListener
{
    /** @var  TokenStorageInterface */
    protected $tokenStorage;

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Order) {
            return;
        }

        if ($entity->getOrganization() !== null) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if ($token === null) {
            throw new \Exception('An order must be created by user.');
        }

        $entity->setOrganization($token->getUser()->getOrganization());
    }
}
