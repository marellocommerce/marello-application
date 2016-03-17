<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Form\Type;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Form\Type\InventoryItemType;
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
        $item = new InventoryItem();

        $form = $this->createForm($item);

        $form->submit([
            'modifyOperator' => InventoryItem::MODIFY_OPERATOR_INCREASE,
            'modifyAmount'   => 10,
        ]);

        $this->assertTrue($form->isValid());
        $this->assertEquals(10, $item->getQuantity());
    }

    /**
     * Test increase from 25 by 10.
     */
    public function testIncrease()
    {
        $item = new InventoryItem();
        $item->setQuantity(25);

        $form = $this->createForm($item);

        $form->submit([
            'modifyOperator' => InventoryItem::MODIFY_OPERATOR_INCREASE,
            'modifyAmount'   => 10,
        ]);

        $this->assertTrue($form->isValid());
        $this->assertEquals(35, $item->getQuantity());
    }

    /**
     * Test decrease from 25 by 10.
     */
    public function testDecrease()
    {
        $item = new InventoryItem();
        $item->setQuantity(25);

        $form = $this->createForm($item);

        $form->submit([
            'modifyOperator' => InventoryItem::MODIFY_OPERATOR_DECREASE,
            'modifyAmount'   => 10,
        ]);

        $this->assertTrue($form->isValid());
        $this->assertEquals(15, $item->getQuantity());
    }

    /**
     * Test decrease from 10 by 20.
     */
    public function testDecreaseUnderZero()
    {
        $item = new InventoryItem();
        $item->setQuantity(10);

        $form = $this->createForm($item);

        $form->submit([
            'modifyOperator' => InventoryItem::MODIFY_OPERATOR_DECREASE,
            'modifyAmount'   => 20,
        ]);

        $this->assertFalse($form->isValid());
    }
}
