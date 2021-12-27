<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\UserBundle\Entity\User;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class InventoryUpdateContextTest extends TestCase
{
    /**
     * @var InventoryUpdateContext
     */
    protected $inventoryUpdateContext;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
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
    public function testSetGetValue($name, $value = null, $expected = null)
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
    public function getSetValueDataProvider()
    {
        $object     = $this->createMock(Product::class);
        $integer    = 10;
        $string     = 'manual';
        $rand       = uniqid();
        $array      = ['array'];

        return [
            'object'    => ['object', $object, $object],
            'rand'      => ['rand', $rand, $rand],
            'array'     => ['array', $array, $array],
            'string'    => ['string', $string, $string],
            'integer'   => ['string', $integer, $integer]
        ];
    }

    /**
     * @dataProvider  getSetDataProvider
     *
     * @param string $property
     * @param mixed  $value
     * @param mixed  $expected
     */
    public function testSetGet($property, $value = null, $expected = null)
    {
        if ($value !== null) {
            call_user_func_array([$this->inventoryUpdateContext, 'set' . ucfirst($property)], [$value]);
        }

        $this->assertEquals(
            $expected,
            call_user_func_array([$this->inventoryUpdateContext, 'get' . ucfirst($property)], [])
        );
    }

    /**
     * @return array
     */
    public function getSetDataProvider()
    {
        $product = $this->createMock(Product::class);
        $inventoryQty = 10;
        $allocatedInventoryQty = 5;
        $changeTrigger = 'manual';
        $user   = $this->createMock(User::class);
        $relatedEntity = $this
            ->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $inventoryItem = $this
            ->getMockBuilder(InventoryItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $inventoryLevel = $this->createMock(InventoryLevel::class);

        return [
            'product'           => ['product', $product, $product],
            'quantity'          => ['inventory', $inventoryQty, $inventoryQty],
            'allocated_inventory_qty' => ['allocatedInventory', $allocatedInventoryQty, $allocatedInventoryQty],
            'change_trigger'    => ['changeTrigger', $changeTrigger, $changeTrigger],
            'user'              => ['user', $user, $user],
            'related_entity'    => ['relatedEntity', $relatedEntity, $relatedEntity],
            'inventory_item'    => ['inventoryItem', $inventoryItem, $inventoryItem],
            'inventory_level'   => ['inventoryLevel', $inventoryLevel, $inventoryLevel],
            'inventory_batches' => ['inventoryBatches', [], []],
            'is_virtual' => ['isVirtual', 0, 0],
        ];
    }
}
