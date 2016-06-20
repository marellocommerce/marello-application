<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Form\Type;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Form\Type\InventoryItemType;
use Marello\Bundle\InventoryBundle\Model\InventoryItemModify;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class InventoryItemTypeTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient();
    }

    /**
     * Creates tested form.
     *
     * @param $data
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    protected function createForm($data)
    {
        return $this->getContainer()
            ->get('form.factory')
            ->create(InventoryItemType::NAME, $data, [
                'csrf_protection' => false,
            ]);
    }

    /**
     * Test increase from 0 by 10.
     */
    public function testIncreaseFromZero()
    {
        $product   = $this->prophesize(Product::class);
        $warehouse = $this->prophesize(Warehouse::class);

        $item = new InventoryItem($warehouse->reveal(), $product->reveal());

        $form = $this->createForm($item);

        $form->submit([
            'stockOperator' => InventoryItemModify::OPERATOR_INCREASE,
            'stock'         => 10,
        ]);

        $this->assertTrue($form->isValid());
        $this->assertEquals(10, $item->getStock());
    }

    /**
     * Test increase from 25 by 10.
     */
    public function testIncrease()
    {
        $product   = $this->prophesize(Product::class);
        $warehouse = $this->prophesize(Warehouse::class);

        $item = InventoryItem::withStockLevel(
            $warehouse->reveal(),
            $product->reveal(),
            25,
            0,
            'import'
        );

        $form = $this->createForm($item);

        $form->submit([
            'stockOperator' => InventoryItemModify::OPERATOR_INCREASE,
            'stock'   => 10,
        ]);

        $this->assertTrue($form->isValid());
        $this->assertEquals(35, $item->getStock());
    }

    /**
     * Test decrease from 25 by 10.
     */
    public function testDecrease()
    {
        $product   = $this->prophesize(Product::class);
        $warehouse = $this->prophesize(Warehouse::class);

        $item = InventoryItem::withStockLevel(
            $warehouse->reveal(),
            $product->reveal(),
            25,
            0,
            'import'
        );

        $form = $this->createForm($item);

        $form->submit([
            'stockOperator' => InventoryItemModify::OPERATOR_DECREASE,
            'stock'   => 10,
        ]);

        $this->assertTrue($form->isValid());
        $this->assertEquals(15, $item->getStock());
    }

    /**
     * Test decrease from 10 by 20.
     */
    public function testDecreaseUnderZero()
    {
        $product   = $this->prophesize(Product::class);
        $warehouse = $this->prophesize(Warehouse::class);

        $item = InventoryItem::withStockLevel(
            $warehouse->reveal(),
            $product->reveal(),
            10,
            0,
            'import'
        );

        $form = $this->createForm($item);

        $form->submit([
            'stockOperator' => InventoryItemModify::OPERATOR_DECREASE,
            'stock'   => 20,
        ]);

        $this->assertTrue($form->isValid());
    }
}
