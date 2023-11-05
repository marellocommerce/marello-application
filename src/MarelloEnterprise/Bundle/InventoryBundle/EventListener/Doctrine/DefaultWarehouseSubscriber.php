<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

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
        $entity = $args->getObject();
        if (!$entity instanceof Warehouse) {
            return;
        }

        if ($args->hasChangedField('default') && $entity->isDefault()) {
            $this->resetDefault($args->getObjectManager(), $entity->getOwner());
        }
    }

    /**
     * {@inheritdoc}
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$entity instanceof Warehouse || !$entity->isDefault()) {
            return;
        }

        $this->resetDefault($args->getObjectManager(), $entity->getOwner());
    }

    /**
     * reset default Warehouse
     * @param EntityManager $em
     * @param OrganizationInterface $organization
     */
    protected function resetDefault(EntityManager $em, OrganizationInterface $organization)
    {
        $qb = $em->createQueryBuilder();
        $qb
            ->update('MarelloInventoryBundle:Warehouse', 'w')
            ->set('w.default', $qb->expr()->literal(false))
            ->where($qb->expr()->eq('w.owner', ':organization'))
            ->setParameter('organization', $organization);

        $qb->getQuery()->execute();
    }
}
