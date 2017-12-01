<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\OrderTax\Mapper;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\TaxBundle\Model\Taxable;
use Marello\Bundle\TaxBundle\OrderTax\Mapper\OrderItemMapper;
use Oro\Component\Testing\Unit\EntityTrait;

class OrderItemMapperTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    const ITEM_ID = 123;
    const ITEM_PRICE_VALUE = 12.34;
    const ITEM_QUANTITY = 12;

    const CONTEXT_KEY = 'context_key';
    const CONTEXT_VALUE = 'context_value';

    /**
     * @var OrderItemMapper
     */
    protected $mapper;

    protected function setUp()
    {
        $this->mapper = new OrderItemMapper(OrderItem::class);
    }

    protected function tearDown()
    {
        unset($this->mapper);
    }

    public function testGetProcessingClassName()
    {
        $this->assertEquals(OrderItem::class, $this->mapper->getProcessingClassName());
    }

    public function testMap()
    {
        $lineItem = $this->createItem(self::ITEM_ID, self::ITEM_QUANTITY, self::ITEM_PRICE_VALUE);

        $taxable = $this->mapper->map($lineItem);

        $this->assertTaxable(
            $taxable,
            self::ITEM_ID,
            self::ITEM_QUANTITY,
            self::ITEM_PRICE_VALUE
        );
    }

    public function testMapWithoutPrice()
    {
        $lineItem = $this->createItem(self::ITEM_ID, self::ITEM_QUANTITY);

        $taxable = $this->mapper->map($lineItem);

        $this->assertTaxable($taxable, self::ITEM_ID, self::ITEM_QUANTITY, 1.0);
    }

    /**
     * @param int $id
     * @param int $quantity
     * @param float $priceValue
     * @return OrderItem
     */
    protected function createItem($id, $quantity, $priceValue = 1.0)
    {
        /** @var SalesChannel $salesChannel */
        $salesChannel = $this->getEntity(SalesChannel::class, ['id' => $id]);
        /** @var Product $product */
        $product = $this->getEntity(Product::class, ['id' => $id]);
        /** @var Order $order */
        $order = $this->getEntity(Order::class, ['id' => $id, 'currency' => 'USD', 'salesChannel' =>$salesChannel]);
        /** @var OrderItem $lineItem */
        $lineItem = $this->getEntity(OrderItem::class, ['id' => $id, 'product' => $product]);
        $lineItem
            ->setQuantity($quantity)
            ->setOrder($order)
            ->setPrice($priceValue);

        return $lineItem;
    }

    /**
     * @param Taxable $taxable
     * @param int $id
     * @param float $quantity
     * @param float $priceValue
     */
    protected function assertTaxable($taxable, $id, $quantity, $priceValue)
    {
        $this->assertInstanceOf(Taxable::class, $taxable);
        $this->assertEquals($id, $taxable->getIdentifier());
        $this->assertEquals($quantity, $taxable->getQuantity());
        $this->assertEquals($priceValue, $taxable->getPrice());
        $this->assertEquals('0', $taxable->getAmount());
        $this->assertEmpty($taxable->getItems());
    }
}
