<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class DefaultWarehouseSubscriber implements EventSubscriber
{

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'preUpdate',
            'prePersist',
        ];
    }

    /**
     * {@inheritdoc}
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Warehouse) {
            return;
        }

        if ($args->hasChangedField('default') && $entity->isDefault()) {
            $em = $args->getEntityManager();
            $this->resetDefault($em);
        }
    }

    /**
     * {@inheritdoc}
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Warehouse || !$entity->isDefault()) {
            return;
        }

        $this->resetDefault($args->getEntityManager());
    }

    /**
     * reset default Warehouse
     * @param EntityManager $em
     */
    protected function resetDefault(EntityManager $em)
    {
        $qb = $em->createQueryBuilder();
        $qb
            ->update('MarelloInventoryBundle:Warehouse', 'w')
            ->set('w.default', $qb->expr()->literal(false));

        $qb->getQuery()->execute();
    }
}
