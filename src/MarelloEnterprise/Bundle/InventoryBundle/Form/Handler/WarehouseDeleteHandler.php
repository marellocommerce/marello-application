<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Handler;

use Doctrine\ORM\EntityNotFoundException;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Handler\DeleteHandler;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use MarelloEnterprise\Bundle\InventoryBundle\Validator\WarehouseDeleteValidator;

class WarehouseDeleteHandler extends DeleteHandler
{
    /** @var  WarehouseDeleteValidator $warehouseDeleteValidator*/
    protected $warehouseDeleteValidator;

    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * @param WarehouseDeleteValidator $warehouseDeleteValidator
     * @param SecurityFacade $securityFacade
     */
    public function __construct(
        WarehouseDeleteValidator $warehouseDeleteValidator,
        SecurityFacade $securityFacade
    ) {
        $this->warehouseDeleteValidator = $warehouseDeleteValidator;
        $this->securityFacade = $securityFacade;
    }

    /**
     * @param $id
     * @param ApiEntityManager $manager
     * @throws \Exception
     */
    public function handleDelete($id, ApiEntityManager $manager)
    {
        /** @var Warehouse $warehouse */
        $warehouse = $manager->find($id);
        if (!$warehouse) {
            throw new EntityNotFoundException();
        }

        if (!$this->securityFacade->isGranted('EDIT', $warehouse->getOwner())) {
            throw new AccessDeniedException();
        }

        if ($this->warehouseDeleteValidator->validate($warehouse)) {
            $em = $manager->getObjectManager();
            $this->processDelete($warehouse, $em);
        } else {
            throw new \Exception('Cannot delete default warehouse.', 500);
        }
    }
}
