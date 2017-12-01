<?php

namespace Marello\Bundle\PackingBundle\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;

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
        if (!($sourceEntity instanceof Order)) {
            throw new \InvalidArgumentException(
                sprintf('Wrong source entity "%s" provided to OrderToPackingSlipMapper', get_class($sourceEntity))
            );
        }
        $packingSlips = [];
        foreach ($this->warehousesProvider->getWarehousesForOrder($sourceEntity) as $result) {
            /** @var Order $sourceEntity */
            $packingSlip = new PackingSlip();
            $data = $this->getData($sourceEntity, PackingSlip::class);
            $data['order'] = $sourceEntity;
            $data['warehouse'] = $result->getWarehouse();
            $data['items'] = $this->getItems($result->getOrderItems());

            $this->assignData($packingSlip, $data);
            $packingSlips[] = $packingSlip;
        }
        return $packingSlips;
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
        $packingSlipData = $this->getData($orderItem, PackingSlipItem::class);
        /** @var Product $product */
        $product = $orderItem->getProduct();
        $packingSlipData['weight'] = $product->getWeight();
        $packingSlipData['orderItem'] = $orderItem;
        $this->assignData($packingSlipItem, $packingSlipData);

        return $packingSlipItem;
    }
}
