<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\OrderTax\Mapper;

use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Component\Testing\Unit\EntityTrait;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\TaxBundle\Model\Taxable;
use Marello\Bundle\TaxBundle\OrderTax\Mapper\OrderItemMapper;
use Marello\Bundle\TaxBundle\OrderTax\Mapper\OrderMapper;

class OrderMapperTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    const ORDER_ID = 123;
    const ORDER_SUBTOTAL = 234.34;

    /**
     * @var OrderMapper
     */
    protected $mapper;

    /**
     * @var OrderItemMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemMapper;

    protected function setUp()
    {
        $this->orderItemMapper = $this
            ->getMockBuilder(OrderItemMapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper = new OrderMapper(Order::class);
        $this->mapper->setOrderItemMapper($this->orderItemMapper);
    }

    protected function tearDown()
    {
        unset($this->mapper, $this->OrderItemMapper);
    }

    public function testGetProcessingClassName()
    {
        $this->assertEquals(Order::class, $this->mapper->getProcessingClassName());
    }

    public function testMap()
    {
        $this->orderItemMapper
            ->expects($this->once())
            ->method('map')
            ->willReturn(new Taxable());

        $order = $this->createOrder(self::ORDER_ID, self::ORDER_SUBTOTAL);

        $taxable = $this->mapper->map($order);

        $this->assertInstanceOf(Taxable::class, $taxable);
        $this->assertEquals(self::ORDER_ID, $taxable->getIdentifier());
        $this->assertEquals('1', $taxable->getQuantity());
        $this->assertEquals('0', $taxable->getPrice());
        $this->assertEquals('234.34', $taxable->getAmount());
        $this->assertEquals($order->getShippingAddress(), $taxable->getTaxationAddress());
        $this->assertNotEmpty($taxable->getItems());
        $this->assertCount(1, $taxable->getItems());
        $this->assertInstanceOf(Taxable::class, $taxable->getItems()->current());
        $this->assertEquals('20', $taxable->getShippingCost());
    }

    /**
     * Create order
     *
     * @param int $id
     * @param float $subtotal
     * @return Order
     */
    protected function createOrder($id, $subtotal)
    {
        $billingAddress = (new MarelloAddress())
            ->setFirstName('FirstName')
            ->setLastName('LastName')
            ->setStreet('street');
        $shippingAddress = (new MarelloAddress())
            ->setFirstName('FirstName')
            ->setLastName('LastName')
            ->setStreet('street');
        $orderItem = (new OrderItem())
            ->setProduct(new Product());
        /** @var Order $order */
        $order = $this->getEntity(Order::class, ['id' => $id]);
        $order
            ->setSubtotal($subtotal)
            ->addItem($orderItem)
            ->setShippingAddress($shippingAddress)
            ->setBillingAddress($billingAddress)
            ->setCurrency('$')
            ->setShippingAmountInclTax(20);

        return $order;
    }
}
