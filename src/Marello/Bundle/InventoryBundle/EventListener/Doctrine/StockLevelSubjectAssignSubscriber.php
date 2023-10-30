<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevelLogRecord;

/**
 * Class StockLevelSubjectAssignSubscriber
 *
 * Responsible for setting subjectType and subjectId fields on InventoryLevelLogRecord.
 * It stores class and id on entity so it can be later retrieved as a reference to subject.
 *
 * @package Marello\Bundle\InventoryBundle\EventListener\Doctrine
 */
class StockLevelSubjectAssignSubscriber implements EventSubscriber
{
    use SetsPropertyValue;

    /**
     * @var array|InventoryLevel[]
     */
    protected $assignSubjects;

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $insertions = $args->getObjectManager()->getUnitOfWork()->getScheduledEntityInsertions();
        $this->assignSubjects = $this->getLevelsToAssign($insertions);

        /*
         * If there are any InventoryLevel entities with subjects set...
         * Begin transaction, which makes this flush not commit changes to DB.
         */
        if (count($this->assignSubjects) > 0) {
            $args->getObjectManager()->beginTransaction();
        }
    }

    /**
     * @param PostFlushEventArgs $args
     *
     * @throws \Exception
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        /*
         * In case there are no subjects to assign...
         * Do nothing.
         */
        if (count($this->assignSubjects) === 0) {
            return;
        }

        /*
         * Assign type and id values for each InventoryLevel with subject present.
         */
        foreach ($this->assignSubjects as $inventoryLevelLogRecord) {
            $id = $inventoryLevelLogRecord->getSubject()->getId();

            /*
             * If subject is and entity with no id, it means it has not been persisted...
             * Therefore, indicate wrong use of subject system.
             */
            if ($id === null) {
                $args->getObjectManager()->rollback();
                throw new \Exception(
                    "Trying to assign an entity that has not been persisted as subject. Persist the entity first."
                );
            }

            /*
             * Set appropriate values and persist changes.
             */
            $this->setPropertyValue($inventoryLevelLogRecord, 'subjectId', $id);
            $this->setPropertyValue(
                $inventoryLevelLogRecord,
                'subjectType',
                ClassUtils::getClass($inventoryLevelLogRecord->getSubject())
            );
            $args->getObjectManager()->persist($inventoryLevelLogRecord);
        }

        /*
         * Flush changes made to InventoryLevels and finish transaction.
         */
        try {
            $args->getObjectManager()->flush();
        } catch (\Exception $e) {
            $args->getObjectManager()->rollback();
            throw $e;
        }
        $args->getObjectManager()->commit();
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
            Events::postFlush,
        ];
    }

    /**
     * Filters all entity insertions into stock levels which need to have subjects handled...
     * And entity has to be InventoryLevel, have subject set and it has to have subjectType and subjectId empty.
     *
     * @param array $insertions
     *
     * @return InventoryLevel[]
     */
    protected function getLevelsToAssign(array $insertions)
    {
        return array_filter($insertions, function ($entity) {
            if (!$entity instanceof InventoryLevelLogRecord) {
                return false;
            }

            if ($entity->getSubject() === null) {
                return false;
            }

            return !(($entity->getSubjectId() !== null) && ($entity->getSubjectType() !== null));
        });
    }
}
