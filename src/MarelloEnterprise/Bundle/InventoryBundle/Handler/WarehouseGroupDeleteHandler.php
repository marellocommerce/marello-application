<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use MarelloEnterprise\Bundle\InventoryBundle\Checker\IsFixedWarehouseGroupChecker;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SoapBundle\Handler\DeleteHandler;

class WarehouseGroupDeleteHandler extends DeleteHandler
{
    /**
     * @var SecurityFacade
     */
    protected $securityFacade;
    
    /**
     * @var IsFixedWarehouseGroupChecker
     */
    protected $checker;

    /**
     * @param SecurityFacade $securityFacade
     * @param IsFixedWarehouseGroupChecker $checker
     */
    public function __construct(SecurityFacade $securityFacade, IsFixedWarehouseGroupChecker $checker)
    {
        $this->securityFacade = $securityFacade;
        $this->checker = $checker;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkPermissions($entity, ObjectManager $em)
    {
        /** @var $entity WarehouseGroup */
        parent::checkPermissions($entity, $em);
        if (!$this->securityFacade->isGranted('EDIT', $entity->getOrganization())) {
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
