<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SoapBundle\Handler\DeleteHandler;

class WarehouseChannelGroupLinkDeleteHandler extends DeleteHandler
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
        /** @var $entity WarehouseChannelGroupLink */
        parent::checkPermissions($entity, $em);
        if (!$this->securityFacade->isGranted('EDIT', $entity->getOrganization())) {
            throw new ForbiddenException('You have no rights to delete this entity');
        }
        if ($entity->isSystem()) {
            throw new ForbiddenException('It is forbidden to delete system Warehouse Channel Group Link');
        }
    }
}
