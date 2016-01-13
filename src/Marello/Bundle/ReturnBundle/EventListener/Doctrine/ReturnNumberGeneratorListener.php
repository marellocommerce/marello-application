<?php

namespace Marello\Bundle\ReturnBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;

class ReturnNumberGeneratorListener
{
    /** @var ReturnEntity[] */
    protected $returns = [];

    /**
     * Collects all returns scheduled for insertion.
     * Only insertion is used because we don't need to generate return numbers in case it is changed later.
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $uow        = $args->getEntityManager()->getUnitOfWork();
        $insertions = $uow->getScheduledEntityInsertions();

        foreach ($insertions as $entity) {
            if ($entity instanceof ReturnEntity) {
                $this->returns[] = $entity;
            }
        }
    }

    /**
     * Updates all returns which have been scheduled for insertion.
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $em             = $args->getEntityManager();
        $changedReturns = $this->updateReturnNumbers($this->returns);

        /*
         * Empty returns array to indicate that all returns have been processed and to prevent loop on flushing.
         */
        $this->returns = [];

        foreach ($changedReturns as $return) {
            $em->persist($return);
        }

        if (!empty($changedReturns)) {
            $em->flush($changedReturns);
        }
    }

    /**
     * Update return numbers for all returns which still don't have it.
     * return number is generated using ID.
     *
     * @param ReturnEntity[] $returns
     *
     * @return ReturnEntity[] Array of changed returns.
     */
    protected function updateReturnNumbers(array $returns)
    {
        $changedReturns = [];
        foreach ($returns as $return) {
            /*
             * Only generate new return number if return has not one assigned yet. It is possible that return number
             * would be set by some kind of external system pushing data to marello using API for example.
             */
            if (!$return->getReturnNumber()) {
                $changedReturns[] = $return->setReturnNumber(sprintf('%09d', $return->getId()));
            }
        }

        return $changedReturns;
    }
}
