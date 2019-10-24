<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Oro\Component\DependencyInjection\ServiceLink;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class AbstractReverseSyncListener
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var ServiceLink
     */
    protected $syncScheduler;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var UnitOfWork
     *
     */
    protected $unitOfWork;

    /**
     * @var array
     */
    protected $processedEntities = [];

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param ServiceLink $schedulerServiceLink
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        ServiceLink $schedulerServiceLink
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->syncScheduler = $schedulerServiceLink;
    }

    /**
     * @param EntityManager $entityManager
     */
    protected function init(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->unitOfWork = $entityManager->getUnitOfWork();

        // check for logged user is for confidence that data changes mes from UI, not from sync process.
        if (!$this->tokenStorage->getToken() || !$this->tokenStorage->getToken()->getUser()) {
            return;
        }
    }
}
