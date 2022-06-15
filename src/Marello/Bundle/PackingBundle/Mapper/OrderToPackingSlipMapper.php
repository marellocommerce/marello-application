<?php

namespace Marello\Bundle\PackingBundle\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class OrderToPackingSlipMapper extends AbstractPackingSlipMapper
{
    /**
     * @var OrderWarehousesProviderInterface
     */
    protected $warehousesProvider;

    public function __construct(
        EntityFieldProvider $entityFieldProvider,
        PropertyAccessorInterface $propertyAccessor,
        OrderWarehousesProviderInterface $warehousesProvider
    ) {
        parent::__construct($entityFieldProvider, $propertyAccessor);
        $this->warehousesProvider = $warehousesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function map($sourceEntity)
    {
        if (!($sourceEntity instanceof Allocation)) {
            throw new \InvalidArgumentException(
                sprintf('Wrong source entity "%s" provided to OrderToPackingSlipMapper', get_class($sourceEntity))
            );
        }
        /** @var Order $sourceEntity */
        $packingSlip = new PackingSlip();
        $data = $this->getData($sourceEntity->getOrder(), PackingSlip::class);
        $data['order'] = $sourceEntity->getOrder();
        $data['warehouse'] = $sourceEntity->getWarehouse();
        $data['items'] = $this->getItems($sourceEntity->getOrder()->getItems());

        $this->assignData($packingSlip, $data);

        return [$packingSlip];
    }

    /**
     * @param Collection $items
     * @return ArrayCollection
     */
    protected function getItems(Collection $items)
    {
        $orderItems = $items->toArray();
        $packingSlipItems = [];
        /** @var OrderItem $item */
        foreach ($orderItems as $item) {
            $packingSlipItems[] = $this->mapItem($item);
        }

        return new ArrayCollection($packingSlipItems);
    }

    /**
     * @param OrderItem $orderItem
     * @return PackingSlipItem
     */
    protected function mapItem(OrderItem $orderItem)
    {
        $packingSlipItem = new PackingSlipItem();
        $packingSlipItemData = $this->getData($orderItem, PackingSlipItem::class);
        /** @var Product $product */
        $product = $orderItem->getProduct();
        $packingSlipItemData['weight'] = ($product->getWeight() * $orderItem->getQuantity());
        $packingSlipItemData['orderItem'] = $orderItem;
        $this->assignData($packingSlipItem, $packingSlipItemData);

        return $packingSlipItem;
    }
}
