<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class WarehouseGroupHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    public function __construct(
        protected ObjectManager $manager,
        protected AclHelper $aclHelper
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function process($data, FormInterface $form, Request $request)
    {
        if (!$data instanceof WarehouseGroup) {
            throw new \InvalidArgumentException('Argument data should be instance of WarehouseGroup entity');
        }
        
        $warehousesBefore = $data->getWarehouses()->toArray();
        $form->setData($data);

        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            $this->submitPostPutRequest($form, $request);

            if ($form->isValid()) {
                $this->onSuccess($data, $warehousesBefore);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param WarehouseGroup $entity
     * @param Warehouse[] $warehousesBefore
     */
    protected function onSuccess(WarehouseGroup $entity, $warehousesBefore)
    {
        $warehousesAfter = $entity->getWarehouses()->toArray();
        $diff = array_filter($warehousesBefore, function ($entity) use ($warehousesAfter) {
            return !in_array($entity, $warehousesAfter);
        });

        $systemGroup = $this->getSystemWarehouseGroup();
        /** @var Warehouse $warehouse */
        foreach ($diff as $warehouse) {
            $warehouse->setGroup($systemGroup);
            // reset consolidation option for this warehouse
            $warehouse->setIsConsolidationWarehouse(false);
            $this->manager->persist($warehouse);
        }
        /** @var Warehouse $warehouseAfter */
        foreach ($warehousesAfter as $warehouseAfter) {
            $warehouseAfter->setGroup($entity);
            $this->manager->persist($warehouseAfter);
        }
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * @return WarehouseGroup
     */
    private function getSystemWarehouseGroup()
    {
        return $this->manager
            ->getRepository(WarehouseGroup::class)
            ->findSystemWarehouseGroup($this->aclHelper);
    }
}
