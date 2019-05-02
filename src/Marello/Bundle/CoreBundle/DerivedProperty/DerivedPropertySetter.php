<?php

namespace Marello\Bundle\CoreBundle\DerivedProperty;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DerivedPropertySetter
{
    /** @var DerivedPropertyAwareInterface[] */
    private $generate = [];

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * DerivedPropertySetter constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em  = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $insertions = $uow->getScheduledEntityInsertions();

        $this->generate = array_filter($insertions, function ($entity) {
            return $entity instanceof DerivedPropertyAwareInterface;
        });

        if (!empty($this->generate)) {
            $em->beginTransaction();
        }
    }

    /**
     * @param PostFlushEventArgs $args
     *
     * @throws \Exception
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (empty($this->generate)) {
            return;
        }

        foreach ($this->generate as $entity) {
            $entity->setDerivedProperty($entity->getId());
        }

        $dispatch = $this->generate;

        try {
            $args->getEntityManager()->flush($this->generate);
        } catch (\Exception $e) {
            $args->getEntityManager()->rollback();
            throw $e;
        }

        $args->getEntityManager()->commit();

        foreach ($dispatch as $entity) {
            $this->eventDispatcher->dispatch(DerivedPropertySetEvent::NAME, new DerivedPropertySetEvent($entity));
        }

        $this->generate = [];
    }
}
