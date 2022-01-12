<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Context\LineItem\Collection\ArrayCollectionDoctrine;

use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\Doctrine\Factory\DoctrinePaymentLineItemCollectionFactory;
use Marello\Bundle\PaymentBundle\Context\PaymentLineItem;
use Marello\Bundle\OrderBundle\Entity\OrderItem;

class DoctrinePaymentLineItemCollectionFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testFactory()
    {
        $paymentLineItems = [
            new PaymentLineItem([]),
            new PaymentLineItem([]),
            new PaymentLineItem([]),
            new PaymentLineItem([]),
        ];

        $collectionFactory = new DoctrinePaymentLineItemCollectionFactory();
        $collection = $collectionFactory->createPaymentLineItemCollection($paymentLineItems);

        $this->assertEquals($paymentLineItems, $collection->toArray());
    }

    public function testFactoryWithException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected: Marello\Bundle\PaymentBundle\Context\PaymentLineItemInterface');
        $paymentLineItems = [
            new OrderItem(),
            new OrderItem(),
            new OrderItem(),
            new OrderItem(),
        ];

        $collectionFactory = new DoctrinePaymentLineItemCollectionFactory();
        $collectionFactory->createPaymentLineItemCollection($paymentLineItems);
    }
}
