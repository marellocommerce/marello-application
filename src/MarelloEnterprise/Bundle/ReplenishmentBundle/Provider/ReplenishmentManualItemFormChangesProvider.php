<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Provider;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ProductBundle\Entity\Product;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;

class ReplenishmentManualItemFormChangesProvider implements FormChangesProviderInterface
{
    const IDENTIFIER_PREFIX = 'item-id-';
    const ITEMS_FIELD = 'manualItems';

    public function __construct(
        protected DoctrineHelper $doctrineHelper
    ) {}

    /**
     * {@inheritdoc}
     */
    public function processFormChanges(FormChangeContextInterface $context)
    {
        $submittedData = $context->getSubmittedData();
        $replenishment = $context->getForm()->getData();
        if (!$replenishment instanceof ReplenishmentOrderConfig) {
            return;
        }

        $data = [];
        $products = $this->extractProducts($submittedData);
        $origins = $this->extractOrigins($submittedData);
        foreach ($submittedData[self::ITEMS_FIELD] as $key => $item) {
            if (empty($item['product'])) {
                continue;
            }

            $identifier = sprintf('%s%s', self::IDENTIFIER_PREFIX, $key);
            $product = $products[$item['product']];
            /** @var InventoryItem $inventoryItem */
            $inventoryItem = $product->getInventoryItems()->first();
            $unit = $inventoryItem->getProductUnit();
            if ($unit) {
                $data[$identifier]['unit'] = $unit->getName();
            }

            if (empty($item['origin'])) {
                continue;
            }

            $origin = $origins[$item['origin']];
            foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                if ($inventoryLevel->getWarehouse() === $origin) {
                    $data[$identifier]['availableQuantity'] = $inventoryLevel->getInventoryQty();
                    break;
                }
            }
        }

        if (!empty($data)) {
            $result = $context->getResult();
            $result[self::ITEMS_FIELD] = $data;
            $context->setResult($result);
        }
    }

    /**
     * @param array $submittedData
     * @return Product[]
     */
    private function extractProducts(array $submittedData): array
    {
        $productIds = [];
        foreach ($submittedData[self::ITEMS_FIELD] as $item) {
            $productIds[] = (int)$item['product'];
        }
        /** @var Product[] $result */
        $result = $this->doctrineHelper
            ->getEntityRepositoryForClass(Product::class)
            ->findBy(['id' => $productIds]);

        $products = [];
        foreach ($result as $product) {
            $products[$product->getId()] = $product;
        }

        return $products;
    }

    /**
     * @param array $submittedData
     * @return Warehouse[]
     */
    private function extractOrigins(array $submittedData): array
    {
        $warehousesIds = [];
        foreach ($submittedData[self::ITEMS_FIELD] as $item) {
            $warehousesIds[] = (int)$item['origin'];
        }
        /** @var Warehouse[] $result */
        $result = $this->doctrineHelper
            ->getEntityRepositoryForClass(Warehouse::class)
            ->findBy(['id' => $warehousesIds]);

        $warehouses = [];
        foreach ($result as $warehouse) {
            $warehouses[$warehouse->getId()] = $warehouse;
        }

        return $warehouses;
    }
}
