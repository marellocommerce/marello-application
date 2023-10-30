<?php

namespace Marello\Bundle\OrderBundle\Twig;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;
use Marello\Bundle\OrderBundle\Model\OrderItemStatusesInterface;
use Marello\Bundle\OrderBundle\Provider\OrderItem\ShippingPreparedOrderItemsForNotificationProvider;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class OrderExtension extends AbstractExtension
{
    const NAME = 'marello_order';

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var ShippingPreparedOrderItemsForNotificationProvider
     */
    private $orderItemsForNotificationProvider;

    /**
     * @var array
     */
    private $orderInvoices;

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'marello_order_can_return',
                [$this, 'canReturn']
            ),
            new TwigFunction(
                'marello_order_item_shipped',
                [$this, 'isShippedOrderItem']
            ),
            new TwigFunction(
                'marello_get_order_item_status',
                [$this, 'findStatusByName']
            ),
            new TwigFunction(
                'marello_get_order_items_for_notification',
                [$this->orderItemsForNotificationProvider, 'getItems']
            ),
            new TwigFunction(
                'marello_get_order_total_paid',
                [$this, 'getOrderTotalPaid']
            ),
            new TwigFunction(
                'marello_get_order_total_due',
                [$this, 'getOrderTotalDue']
            )
        ];
    }

    /**
     * {@inheritdoc}
     * @param Order $order
     * @return boolean
     */
    public function canReturn(Order $order)
    {
        foreach ($order->getItems() as $orderItem) {
            if (!$orderItem->getStatus() ||
                !in_array($orderItem->getStatus()->getId(), $this->getOrderItemStatuses(), true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     * @param OrderItem $orderItem
     * @return bool
     */
    public function isShippedOrderItem(OrderItem $orderItem)
    {
        if (in_array($orderItem->getStatus()->getId(), $this->getOrderItemStatuses(), true)) {
            return true;
        }

        return false;
    }

    /**
     * Get related OrderItem statuses
     * @return array
     */
    protected function getOrderItemStatuses()
    {
        return [
            LoadOrderItemStatusData::DROPSHIPPING,
            OrderItemStatusesInterface::OIS_SHIPPED,
            OrderItemStatusesInterface::OIS_COMPLETE
        ];
    }

    /**
     * @param string $name
     * @return null|object
     */
    public function findStatusByName($name)
    {
        $statusClass = ExtendHelper::buildEnumValueClassName(LoadOrderItemStatusData::ITEM_STATUS_ENUM_CLASS);
        $status = $this->doctrine
            ->getManagerForClass($statusClass)
            ->getRepository($statusClass)
            ->find($name);

        if ($status) {
            return $status;
        }

        return null;
    }

    public function setRegistry(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @param ShippingPreparedOrderItemsForNotificationProvider $itemsForNotificationProvider
     * @return $this
     */
    public function setItemsForNotificationProvider(
        ShippingPreparedOrderItemsForNotificationProvider $itemsForNotificationProvider
    ) {
        $this->orderItemsForNotificationProvider = $itemsForNotificationProvider;

        return $this;
    }

    /**
     * @param Order $order
     * @return AbstractInvoice[]
     */
    private function getOrderInvoices(Order $order)
    {
        if (!isset($this->orderInvoices[$order->getId()])) {
            $this->orderInvoices[$order->getId()] = $this->doctrine
                ->getManagerForClass(AbstractInvoice::class)
                ->getRepository(AbstractInvoice::class)
                ->findBy(['order' => $order]);
        }

        return $this->orderInvoices[$order->getId()];
    }

    /**
     * @param Order $order
     * @return float|int
     */
    public function getOrderTotalPaid(Order $order)
    {
        $orderInvoices = $this->getOrderInvoices($order);
        $totalPaid = 0.0;
        foreach ($orderInvoices as $invoice) {
            $totalPaid += $invoice->getTotalPaid();
        }

        return $totalPaid;
    }
    /**
     * @param Order $order
     * @return float|int
     */
    public function getOrderTotalDue(Order $order)
    {
        return ($order->getGrandTotal() - $this->getOrderTotalPaid($order));
    }
}
