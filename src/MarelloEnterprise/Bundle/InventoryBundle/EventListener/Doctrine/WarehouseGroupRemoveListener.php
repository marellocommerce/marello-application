<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use MarelloEnterprise\Bundle\InventoryBundle\Checker\IsFixedWarehouseGroupChecker;

class WarehouseGroupRemoveListener
{
    public function __construct(
        protected TranslatorInterface $translator,
        protected Session $session,
        protected IsFixedWarehouseGroupChecker $checker,
        protected AclHelper $aclHelper
    ) {
    }
    
    /**
     * @param WarehouseGroup $warehouseGroup
     * @param LifecycleEventArgs $args
     */
    public function preRemove(WarehouseGroup $warehouseGroup, LifecycleEventArgs $args)
    {
        if ($warehouseGroup->isSystem()) {
            $message = $this->translator->trans(
                'marelloenterprise.inventory.messages.error.warehousegroup.system_warehousegroup_deletion'
            );
        }
        if ($this->checker->check($warehouseGroup)) {
            $message = $this->translator->trans(
                'marelloenterprise.inventory.messages.error.warehousegroup.fixed_warehousegroup_deletion'
            );
        }
        if ($warehouseGroup->getWarehouseChannelGroupLink()) {
            $message = $this->translator->trans(
                'marelloenterprise.inventory.messages.error.warehousegroup.linked_warehousegroup_deletion'
            );
        }
        if (isset($message)) {
            $this->session->getFlashBag()->add('error', $message);
            throw new AccessDeniedException($message);
        }
        $em = $args->getEntityManager();
        $systemGroup = $em
            ->getRepository(WarehouseGroup::class)
            ->findSystemWarehouseGroup($this->aclHelper);

        if ($systemGroup) {
            $warehouses = $warehouseGroup->getWarehouses();
            foreach ($warehouses as $warehouse) {
                $warehouse->setGroup($systemGroup);
                $em->persist($warehouse);
            }
            $em->flush();
        }
    }
}
