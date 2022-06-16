<?php

namespace Marello\Bundle\OrderBundle\Factory;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;
use Marello\Bundle\OrderBundle\Converter\OrderPaymentLineItemConverterInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Event\OrderPaymentContextBuildingEvent;
use Marello\Bundle\PaymentBundle\Context\Builder\Factory\PaymentContextBuilderFactoryInterface;
use Marello\Bundle\PaymentBundle\Context\PaymentContextFactoryInterface;
use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderPaymentContextFactory implements PaymentContextFactoryInterface
{
    /**
     * @var OrderWarehousesProviderInterface
     */
    private $orderWarehousesProvider;

    /**
     * @var OrderPaymentLineItemConverterInterface
     */
    private $paymentLineItemConverter;

    /**
     * @var PaymentContextBuilderFactoryInterface|null
     */
    private $paymentContextBuilderFactory;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param OrderWarehousesProviderInterface $orderWarehousesProvider
     * @param OrderPaymentLineItemConverterInterface $paymentLineItemConverter
     * @param EventDispatcherInterface $eventDispatcher
     * @param null|PaymentContextBuilderFactoryInterface $paymentContextBuilderFactory
     */
    public function __construct(
        OrderWarehousesProviderInterface $orderWarehousesProvider,
        OrderPaymentLineItemConverterInterface $paymentLineItemConverter,
        EventDispatcherInterface $eventDispatcher,
        PaymentContextBuilderFactoryInterface $paymentContextBuilderFactory = null
    ) {
        $this->orderWarehousesProvider = $orderWarehousesProvider;
        $this->paymentLineItemConverter = $paymentLineItemConverter;
        $this->eventDispatcher = $eventDispatcher;
        $this->paymentContextBuilderFactory = $paymentContextBuilderFactory;
    }

    /**
     * @param Order $order
     * @return PaymentContextInterface[]
     */
    public function create($order)
    {
        $this->ensureApplicable($order);

        if (null === $this->paymentContextBuilderFactory) {
            return null;
        }

        $paymentContextBuilder = $this->paymentContextBuilderFactory->createPaymentContextBuilder(
            $order,
            (string)$order->getId()
        );

        $results = [];
        $orderItems = $order->getItems();
        $subtotal = 0.00;
        foreach ($orderItems as $orderItem) {
            $subtotal += $orderItem->getPrice() * $orderItem->getQuantity();
        }
        $subtotal = Price::create(
            $subtotal,
            $order->getCurrency()
        );

        $paymentContextBuilder
            ->setSubTotal($subtotal)
            ->setCurrency($order->getCurrency());

        $convertedLineItems = $this->paymentLineItemConverter->convertLineItems($orderItems);
        if (null !== $order->getShippingAddress()) {
            $paymentContextBuilder->setShippingAddress($order->getShippingAddress());
        }

        if (null !== $order->getBillingAddress()) {
            $paymentContextBuilder->setBillingAddress($order->getBillingAddress());
        }

        if (null !== $order->getCustomer()) {
            $paymentContextBuilder->setCustomer($order->getCustomer());
        }

        if (null !== $convertedLineItems) {
            $paymentContextBuilder->setLineItems($convertedLineItems);
        }
        $paymentContext = $paymentContextBuilder->getResult();
        $event = new OrderPaymentContextBuildingEvent($paymentContext);
        $this->eventDispatcher->dispatch($event, OrderPaymentContextBuildingEvent::NAME);
        $results[] = $event->getPaymentContext();

        return $results;
    }

    /**
     * @param object $entity
     * @throws \InvalidArgumentException
     */
    protected function ensureApplicable($entity)
    {
        if (!is_a($entity, Order::class)) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" expected, "%s" given',
                Order::class,
                is_object($entity) ? get_class($entity) : gettype($entity)
            ));
        }
    }

    /**
     * @param Order $order
     * @return MarelloAddress|null
     */
    protected function getShippingOrigin(Order $order)
    {
        $orderWarehouseResults = $this->orderWarehousesProvider->getWarehousesForOrder($order);
        if (!empty($orderWarehouseResults)) {
            /** @var OrderWarehouseResult $orderWarehouseResult */
            $orderWarehouseResult = reset($orderWarehouseResults);

            return $orderWarehouseResult->getWarehouse()->getAddress();
        }

        return null;
    }
}
