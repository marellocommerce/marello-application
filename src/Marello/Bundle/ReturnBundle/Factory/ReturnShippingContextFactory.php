<?php

namespace Marello\Bundle\ReturnBundle\Factory;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\OrderBundle\Converter\OrderShippingLineItemConverterInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Event\OrderShippingContextBuildingEvent;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Marello\Bundle\ShippingBundle\Context\Builder\Factory\ShippingContextBuilderFactoryInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingContextFactoryInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ReturnShippingContextFactory implements ShippingContextFactoryInterface
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
     * @param ReturnEntity $return
     * @return ShippingContextInterface[]
     */
    public function create($return)
    {
        $this->ensureApplicable($return);

        if (null === $this->shippingContextBuilderFactory) {
            return null;
        }

        $shippingContextBuilder = $this->shippingContextBuilderFactory->createShippingContextBuilder(
            $return,
            (string)$return->getId()
        );

        $results = [];
        $returnItems = $return->getReturnItems();
        $subtotal = 0.00;
        $order = $return->getOrder();
        /** @var ReturnItem $returnItem */
        foreach ($returnItems as $returnItem) {
            $subtotal += $returnItem->getOrderItem()->getPrice() * $returnItem->getQuantity();
        }
        $subtotal = Price::create(
            $subtotal,
            $order->getCurrency()
        );

        $shippingContextBuilder
            ->setSubTotal($subtotal)
            ->setCurrency($order->getCurrency());

        $convertedLineItems = $this->shippingLineItemConverter->convertLineItems($order->getItems());

        $shippingOrigin = $this->getShippingOrigin($return);
        if (null !== $shippingOrigin) {
            $shippingContextBuilder->setShippingOrigin($shippingOrigin);
        }
        // address of merchant
        if (null !== $order->getShippingAddress()) {
            $shippingContextBuilder->setShippingAddress($order->getShippingAddress());
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
        if (!is_a($entity, ReturnEntity::class)) {
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
    protected function getShippingOrigin(ReturnEntity $return)
    {
        if ($return->getOrder()) {
            return $return->getOrder()->getShippingAddress();
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
