<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Oro\Bundle\SoapBundle\Handler\DeleteHandler;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use MarelloEnterprise\Bundle\InventoryBundle\Checker\IsFixedWarehouseGroupChecker;

class WarehouseGroupDeleteHandler extends DeleteHandler
{
    /**
     * @var AuthorizationCheckerInterface $authorizationChecker
     */
    protected $authorizationChecker;
    
    /**
     * @var IsFixedWarehouseGroupChecker $checker
     */
    protected $checker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param IsFixedWarehouseGroupChecker $checker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, IsFixedWarehouseGroupChecker $checker)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->checker = $checker;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkPermissions($entity, ObjectManager $em)
    {
        /** @var $entity WarehouseGroup */
        parent::checkPermissions($entity, $em);
        if (!$this->authorizationChecker->isGranted('EDIT', $entity->getOrganization())) {
            throw new ForbiddenException('You have no rights to delete this entity');
        }
        if ($entity->isSystem()) {
            throw new ForbiddenException('It is forbidden to delete system Warehouse Group');
        }
        if ($this->checker->check($entity)) {
            throw new \Exception('It is forbidden to delete Fixed Warehouse(Group)');
        }
        if ($entity->getWarehouseChannelGroupLink()) {
            throw new \Exception('It is forbidden to delete Linked Warehouse(Group), unlink it first');
        }
    }
}
