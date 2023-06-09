<?php

namespace Marello\Bundle\InventoryBundle\Factory;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use Marello\Bundle\OrderBundle\Converter\OrderShippingLineItemConverterInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Event\OrderShippingContextBuildingEvent;
use Marello\Bundle\ShippingBundle\Context\Builder\Factory\ShippingContextBuilderFactoryInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingContextFactoryInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AllocationShippingContextFactory implements ShippingContextFactoryInterface
{
    /**
     * @var OrderShippingLineItemConverterInterface
     */
    private $shippingLineItemConverter;

    /**
     * @var ShippingContextBuilderFactoryInterface|null
     */
    private $shippingContextBuilderFactory;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param OrderShippingLineItemConverterInterface $shippingLineItemConverter
     * @param EventDispatcherInterface $eventDispatcher
     * @param null|ShippingContextBuilderFactoryInterface $shippingContextBuilderFactory
     */
    public function __construct(
        OrderShippingLineItemConverterInterface $shippingLineItemConverter,
        ShippingContextBuilderFactoryInterface $shippingContextBuilderFactory = null
    ) {
        $this->shippingLineItemConverter = $shippingLineItemConverter;
        $this->shippingContextBuilderFactory = $shippingContextBuilderFactory;
    }

    /**
     * @param Allocation $allocation
     * @return ShippingContextInterface[]
     */
    public function create($allocation)
    {
        $this->ensureApplicable($allocation);

        if (null === $this->shippingContextBuilderFactory) {
            return null;
        }

        $shippingContextBuilder = $this->shippingContextBuilderFactory->createShippingContextBuilder(
            $allocation,
            (string)$allocation->getId()
        );

        $results = [];
        $allocationItems = $allocation->getItems();
        $subtotal = 0.00;
        $order = $allocation->getOrder();
        /** @var AllocationItem $allocationItem */
        foreach ($allocationItems as $allocationItem) {
            $subtotal += $allocationItem->getOrderItem()->getPrice() * $allocationItem->getQuantityConfirmed();
        }
        $subtotal = Price::create(
            $subtotal,
            $order->getCurrency()
        );

        $shippingContextBuilder
            ->setSubTotal($subtotal)
            ->setCurrency($order->getCurrency());

        $convertedLineItems = $this->shippingLineItemConverter->convertLineItems($order->getItems());

        $shippingOrigin = $this->getShippingOrigin($allocation);
        if (null !== $shippingOrigin) {
            $shippingContextBuilder->setShippingOrigin($shippingOrigin);
        }
        if (null !== $allocation->getShippingAddress()) {
            $shippingContextBuilder->setShippingAddress($allocation->getShippingAddress());
        }

        if (null !== $order->getBillingAddress()) {
            $shippingContextBuilder->setBillingAddress($order->getBillingAddress());
        }

        if (null !== $order->getCustomer()) {
            $shippingContextBuilder->setCustomer($order->getCustomer());
        }

        if (null !== $convertedLineItems) {
            $shippingContextBuilder->setLineItems($convertedLineItems);
        }
        $shippingContext = $shippingContextBuilder->getResult();
        $event = new OrderShippingContextBuildingEvent($shippingContext);
        $this->eventDispatcher->dispatch($event, OrderShippingContextBuildingEvent::NAME);
        $results[] = $event->getShippingContext();

        return $results;
    }

    /**
     * @param object $entity
     * @throws \InvalidArgumentException
     */
    protected function ensureApplicable($entity)
    {
        if (!is_a($entity, Allocation::class)) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" expected, "%s" given',
                Order::class,
                is_object($entity) ? get_class($entity) : gettype($entity)
            ));
        }
    }

    /**
     * @param Allocation $allocation
     * @return MarelloAddress|null
     */
    protected function getShippingOrigin(Allocation $allocation)
    {
        if ($warehouse = $allocation->getWarehouse()) {
            return $warehouse->getAddress();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
