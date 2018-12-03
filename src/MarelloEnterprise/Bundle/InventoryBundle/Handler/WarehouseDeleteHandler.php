<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Oro\Bundle\SoapBundle\Handler\DeleteHandler;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class WarehouseDeleteHandler extends DeleteHandler
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkPermissions($entity, ObjectManager $em)
    {
        /** @var $entity Warehouse */
        parent::checkPermissions($entity, $em);
        if (!$this->authorizationChecker->isGranted('EDIT', $entity->getOwner())) {
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
