<?php

namespace Marello\Bundle\ProductBundle\Validator;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ProductSupplierRelationsDropshipValidator extends ConstraintValidator
{
    public function __construct(
        protected ManagerRegistry $doctrine
    ) {
    }

    /**
     * Checks if the passed entity is unique in collection.
     * @param mixed $entity
     * @param Constraint $constraint
     * @throws UnexpectedTypeException
     */
    public function validate($entity, Constraint $constraint)
    {
        if ($entity instanceof Product && $entity->getId()) {
            $existingSupplierRelationsCollection = $this->doctrine
                ->getManagerForClass(ProductSupplierRelation::class)
                ->getRepository(ProductSupplierRelation::class)
                ->findBy(['product' => $entity->getId()]);
            $existingSupplierRelations = $this->makeSupplierRelationsByIdsArray($existingSupplierRelationsCollection);
            $supplierRelations = $this->makeSupplierRelationsByIdsArray($entity->getSuppliers()->toArray());
            foreach ($existingSupplierRelations as $id => $existingSupplierRelation) {
                if ((!isset($supplierRelations[$id]) && $existingSupplierRelation->getCanDropship()) ||
                        (isset($supplierRelations[$id]) && !$supplierRelations[$id]->getCanDropship())) {
                    $this->onProductDropshipOff($existingSupplierRelation, $constraint);
                }
            }
        }
    }

    /**
     * @param array $supplierRelations
     * @return array
     */
    private function makeSupplierRelationsByIdsArray(array $supplierRelations)
    {
        $supplierRelationsByIds = [];
        foreach ($supplierRelations as $supplierRelation) {
            if ($id = $supplierRelation->getId()) {
                $supplierRelationsByIds[$id] = $supplierRelation;
            }
        }
        
        return $supplierRelationsByIds;
    }

    /**
     * @param ProductSupplierRelation $productSupplierRelation
     * @param Constraint $constraint
     */
    private function onProductDropshipOff(ProductSupplierRelation $productSupplierRelation, Constraint $constraint)
    {
        $warehouse = $this->getWarehouse($productSupplierRelation->getSupplier());
        if ($warehouse) {
            $inventoryItem = $this->doctrine
                ->getManagerForClass(InventoryItem::class)
                ->getRepository(InventoryItem::class)
                ->findOneByProduct($productSupplierRelation->getProduct());

            $inventoryLevel = $inventoryItem->getInventoryLevel($warehouse);
            if ($inventoryLevel) {
                if ($inventoryLevel->getInventoryQty() > 0 || $inventoryLevel->getAllocatedInventoryQty() > 0) {
                    $this->context->buildViolation($constraint->message)
                        ->atPath('suppliers')
                        ->addViolation();
                }
            }
        }
    }

    /**
     * @param Supplier $supplier
     * @return Warehouse
     */
    private function getWarehouse(Supplier $supplier)
    {
        $warehouseType = $this->doctrine
            ->getManagerForClass(WarehouseType::class)
            ->getRepository(WarehouseType::class)
            ->find(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL);
        $warehouse = $this->doctrine
            ->getManagerForClass(Warehouse::class)
            ->getRepository(Warehouse::class)
            ->findOneBy([
                'code' => $supplier->getCode(),
                'warehouseType' => $warehouseType
            ]);

        return $warehouse;
    }
}
