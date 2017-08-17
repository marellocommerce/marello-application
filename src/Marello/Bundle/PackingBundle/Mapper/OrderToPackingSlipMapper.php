<?php

namespace Marello\Bundle\PackingBundle\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class OrderToPackingSlipMapper extends AbstractPackingSlipMapper
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    public function __construct(
        EntityFieldProvider $entityFieldProvider,
        PropertyAccessorInterface $propertyAccessor,
        DoctrineHelper $doctrineHelper
    ) {
        parent::__construct($entityFieldProvider, $propertyAccessor);
        $this->doctrineHelper = $doctrineHelper;
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
        /** @var Order $sourceEntity */
        $packingSlip = new PackingSlip();
        $data = $this->getData($sourceEntity, PackingSlip::class);
        $data['order'] = $sourceEntity;
        $data['warehouse'] = $this->getWarehouse();
        $data['items'] = $this->getItems($sourceEntity);

        $this->assignData($packingSlip, $data);

        return [$packingSlip];
    }

    /**
     * @param Order $entity
     * @return ArrayCollection
     */
    protected function getItems(Order $entity)
    {
        $orderItems = $entity->getItems()->toArray();
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
        $packingSlipData['weight'] = $orderItem->getProduct()->getWeight();
        $packingSlipData['orderItem'] = $orderItem;
        $this->assignData($packingSlipItem, $packingSlipData);

        return $packingSlipItem;
    }

    /**
     * @return Warehouse
     */
    protected function getWarehouse()
    {
        return $this->doctrineHelper
            ->getEntityManagerForClass(Warehouse::class)
            ->getRepository(Warehouse::class)
            ->getDefault();
    }
}
