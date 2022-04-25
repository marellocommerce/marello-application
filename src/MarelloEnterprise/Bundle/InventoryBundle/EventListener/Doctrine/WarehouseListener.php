<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

class WarehouseListener
{
    public function __construct(
        protected $installed,
        protected TranslatorInterface $translator,
        protected Session $session,
        protected AclHelper $aclHelper
    ) {}

    /**
     * @param Warehouse $warehouse
     * @param LifecycleEventArgs $args
     */
    public function prePersist(Warehouse $warehouse, LifecycleEventArgs $args)
    {
        if ($this->installed && !$warehouse->getGroup()) {
            $em = $args->getEntityManager();
            $whType = $warehouse->getWarehouseType();
            if ($whType && $whType->getName() === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_FIXED) {
                $group = new WarehouseGroup();
                $group
                    ->setName($warehouse->getLabel())
                    ->setOrganization($warehouse->getOwner())
                    ->setDescription(sprintf('%s group', $warehouse->getLabel()))
                    ->setSystem(false);
                $em->persist($group);
                $em->flush($group);
            } else {
                $group = $em
                    ->getRepository(WarehouseGroup::class)
                    ->findSystemWarehouseGroup($this->aclHelper);
            }

            if ($group) {
                $warehouse->setGroup($group);
            }
        }
    }

    /**
     * @param Warehouse $warehouse
     * @param LifecycleEventArgs $args
     */
    public function preRemove(Warehouse $warehouse, LifecycleEventArgs $args)
    {
        if ($warehouse->isDefault()) {
            $message = $this->translator->trans(
                'marelloenterprise.inventory.messages.error.warehouse.default_warehouse_deletion'
            );
        }
        $inventoryLevels = $args
            ->getEntityManager()
            ->getRepository(InventoryLevel::class)
            ->findBy(['warehouse' => $warehouse]);
        if (!empty($inventoryLevels)) {
            $message = $this->translator->trans(
                'marelloenterprise.inventory.messages.error.warehouse.warehouse_with_inventory_deletion'
            );
        }
        if (isset($message)) {
            $this->session->getFlashBag()->add('error', $message);
            throw new ForbiddenException($message); // weedizp2
        }
        if ($group = $warehouse->getGroup()) {
            if (!$group->isSystem() && $group->getWarehouses()->count() < 1) {
                $args->getEntityManager()->remove($group);
            }
        }
    }
}
