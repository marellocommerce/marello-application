<?php

namespace Marello\Bundle\TaxBundle\OrderTax\Mapper;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\TaxBundle\Mapper\TaxMapperInterface;

abstract class AbstractOrderMapper implements TaxMapperInterface
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = (string)$className;
    }

    /**
     * @param Order $order
     * @return AbstractAddress
     */
    public function getTaxationAddress(Order $order)
    {
        return $order->getShippingAddress();
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessingClassName()
    {
        return $this->className;
    }
}
