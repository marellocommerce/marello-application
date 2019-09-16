<?php

namespace Marello\Bundle\OrderBundle\Twig;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;
use Marello\Bundle\OrderBundle\Provider\OrderItem\ShippingPreparedOrderItemsForNotificationProvider;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class OrderExtension extends \Twig_Extension
{
    const NAME = 'marello_order';

    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var ShippingPreparedOrderItemsForNotificationProvider
     */
    private $orderItemsForNotificationProvider;

    /**
     * @param Registry $doctrine
     * @param ShippingPreparedOrderItemsForNotificationProvider $orderItemsForNotificationProvider
     */
    public function __construct(
        Registry $doctrine,
        ShippingPreparedOrderItemsForNotificationProvider $orderItemsForNotificationProvider
    ) {
        $this->doctrine = $doctrine;
        $this->orderItemsForNotificationProvider = $orderItemsForNotificationProvider;
    }

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
            new \Twig_SimpleFunction(
                'marello_order_can_return',
                [$this, 'canReturn']
            ),
            new \Twig_SimpleFunction(
                'marello_order_item_shipped',
                [$this, 'isShippedOrderItem']
            ),
            new \Twig_SimpleFunction(
                'marello_get_order_item_status',
                [$this, 'findStatusByName']
            ),
            new \Twig_SimpleFunction(
                'marello_get_order_items_for_notification',
                [$this->orderItemsForNotificationProvider, 'getItems']
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
            if (!in_array($orderItem->getStatus(), $this->getOrderItemStatuses())) {
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
        if (in_array($orderItem->getStatus(), $this->getOrderItemStatuses())) {
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
            LoadOrderItemStatusData::SHIPPED
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
}
