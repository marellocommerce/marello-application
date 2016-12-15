<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Model;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Oro\Bundle\UserBundle\Entity\User;

class InventoryUpdateContextTest extends WebTestCase
{
    /** @var InventoryUpdateContext $inventoryUpdateContext */
    protected $inventoryUpdateContext;

    public function setUp()
    {
        $this->inventoryUpdateContext = new InventoryUpdateContext();
    }

    /**
     * @dataProvider  getSetDataProvider
     *
     * @param string $name
     * @param mixed  $value
     * @param mixed  $expected
     */
    public function testSetGet($name, $value = null, $expected = null)
    {
        if ($value !== null) {
            call_user_func_array([$this->inventoryUpdateContext, 'setValue'], [$name, $value]);
        }

        $this->assertEquals($expected, call_user_func_array([$this->inventoryUpdateContext, 'getValue'], [$name]));
    }

    /**
     * test getting non-existing value
     */
    public function testSetGetNonExistingValue()
    {
        $this->assertNull($this->inventoryUpdateContext->getValue('no_name'));
    }

    /**
     * @return array
     */
    public function getSetDataProvider()
    {
        $product = $this->getMock(Product::class);
        $stock         = 10;
        $allocatedStock          = 5;
        $changeTrigger = 'manual';
        $user   = $this->getMock(User::class);
        $relatedEntity = $this
            ->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $inventoryItem = $this
            ->getMockBuilder(InventoryItem::class)
            ->disableOriginalConstructor()
            ->getMock();


        return [
            'product'           => ['product', $product, $product],
            'stock'             => ['stock', $stock, $stock],
            'allocated_stock'   => ['allocated_stock', $allocatedStock, $allocatedStock],
            'change_trigger'    => ['change_trigger', $changeTrigger, $changeTrigger],
            'user'              => ['user', $user, $user],
            'related_entity'    => ['related_entity', $relatedEntity, $relatedEntity],
            'updated_items'     => ['updated_items', [$inventoryItem], [$inventoryItem]]
        ];
    }
}
