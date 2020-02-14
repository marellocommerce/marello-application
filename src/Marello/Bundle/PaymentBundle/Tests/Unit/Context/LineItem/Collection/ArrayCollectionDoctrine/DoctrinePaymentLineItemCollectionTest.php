<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Context\LineItem\Collection\ArrayCollectionDoctrine;

use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\Doctrine\DoctrinePaymentLineItemCollection;
use Marello\Bundle\PaymentBundle\Context\PaymentLineItem;

class DoctrinePaymentLineItemCollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testCollection()
    {
        $paymentLineItems = [
            new PaymentLineItem([]),
            new PaymentLineItem([]),
            new PaymentLineItem([]),
            new PaymentLineItem([]),
        ];

        $collection = new DoctrinePaymentLineItemCollection($paymentLineItems);

        $this->assertEquals($paymentLineItems, $collection->toArray());
    }
}
