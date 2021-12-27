<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Context\LineItem\Collection\ArrayCollectionDoctrine;

use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\Doctrine\Factory\DoctrineShippingLineItemCollectionFactory;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItem;
use Marello\Bundle\OrderBundle\Entity\OrderItem;

class DoctrineShippingLineItemCollectionFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testFactory()
    {
        $shippingLineItems = [
            new ShippingLineItem([]),
            new ShippingLineItem([]),
            new ShippingLineItem([]),
            new ShippingLineItem([]),
        ];

        $collectionFactory = new DoctrineShippingLineItemCollectionFactory();
        $collection = $collectionFactory->createShippingLineItemCollection($shippingLineItems);

        $this->assertEquals($shippingLineItems, $collection->toArray());
    }

    public function testFactoryWithException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected: Marello\Bundle\ShippingBundle\Context\ShippingLineItemInterface');
        $shippingLineItems = [
            new OrderItem(),
            new OrderItem(),
            new OrderItem(),
            new OrderItem(),
        ];

        $collectionFactory = new DoctrineShippingLineItemCollectionFactory();
        $collectionFactory->createShippingLineItemCollection($shippingLineItems);
    }
}
