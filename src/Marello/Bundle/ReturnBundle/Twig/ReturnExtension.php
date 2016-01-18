<?php

namespace Marello\Bundle\ReturnBundle\Twig;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ReturnBundle\Util\ReturnHelper;

class ReturnExtension extends \Twig_Extension
{
    /** @var ReturnHelper */
    protected $returnHelper;

    /**
     * ReturnExtension constructor.
     *
     * @param ReturnHelper $returnHelper
     */
    public function __construct(ReturnHelper $returnHelper)
    {
        $this->returnHelper = $returnHelper;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'marello_return';
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
                'marello_return_get_order_item_returned_quantity',
                [$this, 'getOrderItemReturnedQuantity']
            ),
        ];
    }

    /**
     * @param OrderItem $orderItem
     *
     * @return int
     */
    public function getOrderItemReturnedQuantity(OrderItem $orderItem)
    {
        return $this->returnHelper->getOrderItemReturnedQuantity($orderItem);
    }
}
