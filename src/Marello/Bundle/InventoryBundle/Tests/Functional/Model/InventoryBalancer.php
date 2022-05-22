<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Model;

use Doctrine\ORM\NoResultException;

use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;

use Marello\Bundle\InventoryBundle\Model\BalancedInventory\BalancedInventoryHandler;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;

class InventoryBalancer extends WebTestCase
{
    use MessageQueueExtension;

    /** @var InventoryBalancer $inventoryBalancer */
    protected $inventoryBalancer;

    /** @var BalancedInventoryHandler $balancedInventoryHandler */
    protected $balancedInventoryHandler;

    public function setUp(): void
    {
        $this->markTestIncomplete();
        $this->initClient($this->generateBasicAuthHeader());

        $this->loadFixtures(
            [
                LoadProductData::class,
            ]
        );

        $this->inventoryBalancer = $this->getContainer()
            ->get('marello_inventory.model.balancer.inventory_balancer');
        $this->balancedInventoryHandler = $this->getContainer()
            ->get('marello_inventory.model.balancedinventory.balanced_inventory_handler');
    }

    /**
     * @throws NoResultException
     * @throws \Oro\Bundle\NotificationBundle\Exception\NotificationSendException
     */
    public function testExceptionIsThrownWhenTemplateIsNotFoundForEntity()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);
        // todo
    }

    /**
     * test if the message is sent to the consumer with the subject and content rendered instead of plain text
     * without the dynamic attributes like `entity.orderNumber`
     */
    public function testMessageSendIsRenderedTemplateAndSubject()
    {

    }
}
