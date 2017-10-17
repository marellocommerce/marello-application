<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SoapBundle\Handler\DeleteHandler;

class WarehouseDeleteHandler extends DeleteHandler
{
    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @param SecurityFacade $securityFacade
     */
    public function __construct(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkPermissions($entity, ObjectManager $em)
    {
        /** @var $entity Warehouse */
        parent::checkPermissions($entity, $em);
        if (!$this->securityFacade->isGranted('EDIT', $entity->getOwner())) {
            throw new ForbiddenException('You have no rights to delete this entity');
        }
        if ($entity->isDefault()) {
            throw new ForbiddenException('It is forbidden to delete default Warehouse');
        }
    }

    /**
     * Deletes the given entity
     *
     * @param object        $entity
     * @param ObjectManager $em
     */
    protected function deleteEntity($entity, ObjectManager $em)
    {
        if ($entity instanceof Warehouse) {
            if ($group = $entity->getGroup()) {
                $em->remove($entity);
                if (!$group->isSystem() && $group->getWarehouses()->count() <= 1) {
                    $em->remove($group);
                }
            }
        }
    }
}
