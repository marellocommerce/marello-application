<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Oro\Bundle\SoapBundle\Handler\DeleteHandler;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;

class WarehouseDeleteHandler extends DeleteHandler
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TranslatorInterface $translator
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, TranslatorInterface $translator)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkPermissions($entity, ObjectManager $em)
    {
        /** @var $entity Warehouse */
        parent::checkPermissions($entity, $em);
        if (!$this->authorizationChecker->isGranted('EDIT', $entity->getOwner())) {
            throw new ForbiddenException(
                $this->translator->trans('marelloenterprise.inventory.messages.error.warehouse.no_rights_to_delete')
            );
        }
        if ($entity->isDefault()) {
            throw new ForbiddenException(
                $this->translator->trans(
                    'marelloenterprise.inventory.messages.error.warehouse.default_warehouse_deletion'
                )
            );
        }
        $inventoryLevels = $em->getRepository(InventoryLevel::class)->findBy(['warehouse' => $entity]);
        if (!empty($inventoryLevels)) {
            throw new \Exception(
                $this->translator->trans(
                    'marelloenterprise.inventory.messages.error.warehouse.warehouse_with_inventory_deletion'
                )
            );
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
