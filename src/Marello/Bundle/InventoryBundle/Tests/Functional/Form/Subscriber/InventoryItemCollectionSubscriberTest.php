<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Form\Subscriber;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Symfony\Component\Form\FormEvent;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Form\Subscriber\InventoryItemCollectionSubscriber;

class InventoryItemCollectionSubscriberTest extends WebTestCase
{
    /** @var InventoryItemCollectionSubscriber */
    protected $subscriber;

    public function setUp()
    {
        $this->initClient();

        $this->subscriber = $this->getContainer()->get('marello_inventory.form.subscriber.inventory_item_collection');
    }

    /**
     * Test if inventory items are generated when there are none.
     */
    public function testInitializeCollectionWhenEmpty()
    {
        $form  = $this->getMock('\Symfony\Component\Form\FormInterface');
        $event = new FormEvent($form, null);

        $this->subscriber->initializeCollection($event);

        $data = $event->getData();

        $this->assertCount(1, $data, 'There should be one inventory item present (for default WH).');
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $data, 'Data should be Collection.');

        /** @var InventoryItem $item */
        $item = $data->first();

        $this->assertInstanceOf('\Marello\Bundle\InventoryBundle\Entity\InventoryItem', $item);
        $this->assertEquals(0, $item->getQuantity(), 'New item should have 0 quantity.');
    }

    /**
     * Test if inventory items are kept.
     */
    public function testInitializeCollectionWhenNotEmpty()
    {
        $form = $this->getMock('\Symfony\Component\Form\FormInterface');

        $defaultWarehouse = $this->getContainer()
            ->get('doctrine')
            ->getRepository('MarelloInventoryBundle:Warehouse')
            ->getDefault();

        /*
         * Collection of one inventory item for default warehouse.
         */
        $initialData = new ArrayCollection([
            (new InventoryItem())->setQuantity(99)->setWarehouse($defaultWarehouse),
        ]);

        $event = new FormEvent($form, $initialData);

        $this->subscriber->initializeCollection($event);

        $data = $event->getData();

        $this->assertCount(1, $data, 'One inventory item from initial data should be kept.');
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $data, 'Data should be Collection.');

        /** @var InventoryItem $item */
        $item = $data->first();

        $this->assertInstanceOf('\Marello\Bundle\InventoryBundle\Entity\InventoryItem', $item);
        $this->assertEquals(99, $item->getQuantity(), 'Item should have the same quantity.');
    }
}
