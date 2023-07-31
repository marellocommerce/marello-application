<?php

namespace Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Workflow\Action;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\EventDispatcher\EventDispatcher;

use PHPUnit\Framework\TestCase;

use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Component\ConfigExpression\Tests\Unit\Fixtures\ItemStub;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Marello\Bundle\PurchaseOrderBundle\Processor\NoteActivityProcessor;
use Marello\Bundle\PurchaseOrderBundle\Workflow\Action\ReceivePurchaseOrderAction;

class ReceivePurchaseOrderActionTest extends TestCase
{
    /** @var ReceivePurchaseOrderAction $action */
    protected $action;

    /** @var ObjectManager $entityManager */
    protected $entityManager;

    /** @var ContextAccessor $contextAccessor */
    protected $contextAccessor;

    /** @var NoteActivityProcessor $noteActivityProcessor */
    protected $noteActivityProcessor;

    public function setUp(): void
    {
        $this->contextAccessor = new ContextAccessor();

        $this->noteActivityProcessor = $this
            ->getMockBuilder(NoteActivityProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager = $this
            ->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ReceivePurchaseOrderAction action */
        $this->action = new ReceivePurchaseOrderAction(
            $this->contextAccessor,
            $this->entityManager,
            $this->noteActivityProcessor
        );

        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->action->setDispatcher($dispatcher);
    }

    /**
     * Call protected methods for testing
     * @param $obj
     * @param $name
     * @param array $args
     * @return mixed
     */
    protected static function callMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        return $method->invokeArgs($obj, $args);
    }

    /**
     * Get Symfony PropertyPath
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getPropertyPath()
    {
        return $this->getMockBuilder(PropertyPath::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testInitializeExceptionNoEntityObject()
    {
        $this->expectException(\Oro\Component\Action\Exception\InvalidParameterException::class);
        $this->expectExceptionMessage('Parameter "entity" is required.');
        $this->action->initialize([]);
    }

    public function testInitializeExceptionEntityNotValidProperty()
    {
        $this->expectException(\Oro\Component\Action\Exception\InvalidParameterException::class);
        $this->expectExceptionMessage('Entity must be valid property definition.');
        $this->action->initialize(['entity' => 1]);
    }

    /**
     * Test entity options
     */
    public function testInitializeEntityOnly()
    {
        $options = ['entity' => $this->getPropertyPath()];
        $this->action->initialize($options);
    }

    /**
     * Test entity and is_partial options
     */
    public function testInitialize()
    {
        $options = ['entity' => $this->getPropertyPath(), 'is_partial' => false];
        $this->action->initialize($options);
    }

    public function testExecuteFullReceived()
    {
        $purchaseOrderMock = $this
            ->getMockBuilder(PurchaseOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $purchaseOrderItemMock = $this
            ->getMockBuilder(PurchaseOrderItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $purchaseOrderItemMock2 = $this
            ->getMockBuilder(PurchaseOrderItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collection = new ArrayCollection();
        $collection->add($purchaseOrderItemMock);
        $collection->add($purchaseOrderItemMock2);

        $purchaseOrderMock->expects($this->once())
            ->method('getItems')
            ->willReturn($collection);

        $purchaseOrderItemMock->expects($this->atMost(2))
            ->method('getOrderedAmount')
            ->willReturn(10);

        $purchaseOrderItemMock->expects($this->atMost(2))
            ->method('getReceivedAmount')
            ->willReturn(10);

        $purchaseOrderItemMock2->expects($this->atMost(2))
            ->method('getOrderedAmount')
            ->willReturn(10);

        $purchaseOrderItemMock2->expects($this->atMost(2))
            ->method('getReceivedAmount')
            ->willReturn(10);

        $purchaseOrderItemMock->expects($this->once())
            ->method('setStatus')
            ->with('complete');

        $purchaseOrderItemMock2->expects($this->once())
            ->method('setStatus')
            ->with('complete');

        $options = ['entity' => new PropertyPath('test_entity')];
        $contextData = ['test_entity' => $purchaseOrderMock];
        $context = new ItemStub($contextData);
        $this->action->initialize($options);
        $this->action->execute($context);
    }

    public function testExecutePartial()
    {
        $purchaseOrderMock = $this
            ->getMockBuilder(PurchaseOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $purchaseOrderItemMock = $this
            ->getMockBuilder(PurchaseOrderItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $purchaseOrderItemMock2 = $this
            ->getMockBuilder(PurchaseOrderItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock = $this
            ->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $inventoryItemMock = $this
            ->createMock(InventoryItem::class);

        $collection = new ArrayCollection();
        $collection->add($purchaseOrderItemMock);
        $collection->add($purchaseOrderItemMock2);

        $purchaseOrderMock->expects($this->once())
            ->method('getItems')
            ->willReturn($collection);

        $purchaseOrderItemMock->expects($this->once())
            ->method('getData')
            ->willReturn(['last_partially_received_qty' => 10]);

        $purchaseOrderItemMock->expects($this->once())
            ->method('setData')
            ->with(null);

        $purchaseOrderItemMock2->expects($this->once())
            ->method('getData')
            ->willReturn(['last_partially_received_qty' => 10]);

        $purchaseOrderItemMock2->expects($this->once())
            ->method('setData')
            ->with(null);

        $purchaseOrderItemMock->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($productMock);

        $purchaseOrderItemMock2->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($productMock);

        $productMock->expects($this->atLeastOnce())
            ->method('getInventoryItem')
            ->willReturn($inventoryItemMock);

        $inventoryItemMock->expects($this->atLeastOnce())
            ->method('setBackOrdersDatetime')
            ->with(null);

        $inventoryItemMock->expects($this->atLeastOnce())
            ->method('setCanPreorder')
            ->with(false);

        $inventoryItemMock->expects($this->atLeastOnce())
            ->method('setPreOrdersDatetime')
            ->with(null);

        $items[] = ['qty' => 10, 'item' => $purchaseOrderItemMock];
        $items[] = ['qty' => 10, 'item' => $purchaseOrderItemMock2];

        $this->noteActivityProcessor
            ->expects($this->once())
            ->method('addNote')
            ->with($purchaseOrderMock, $items);

        $options = ['entity' => new PropertyPath('test_entity'), 'is_partial' => true];
        $contextData = ['test_entity' => $purchaseOrderMock];
        $context = new ItemStub($contextData);
        $this->action->initialize($options);
        $this->action->execute($context);
    }

    public function testExecuteOneItemPartial()
    {
        $purchaseOrderMock = $this
            ->getMockBuilder(PurchaseOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $purchaseOrderItemMock = $this
            ->getMockBuilder(PurchaseOrderItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $purchaseOrderItemMock2 = $this
            ->getMockBuilder(PurchaseOrderItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock = $this
            ->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $inventoryItemMock = $this
            ->createMock(InventoryItem::class);

        $collection = new ArrayCollection();
        $collection->add($purchaseOrderItemMock);
        $collection->add($purchaseOrderItemMock2);

        $purchaseOrderMock->expects($this->once())
            ->method('getItems')
            ->willReturn($collection);

        $purchaseOrderItemMock->expects($this->once())
            ->method('getData')
            ->willReturn(['last_partially_received_qty' => 10]);

        $purchaseOrderItemMock->expects($this->once())
            ->method('setData')
            ->with(null);

        $purchaseOrderItemMock2->expects($this->once())
            ->method('getData')
            ->willReturn([]);

        $purchaseOrderItemMock->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($productMock);

        $productMock->expects($this->atLeastOnce())
            ->method('getInventoryItem')
            ->willReturn($inventoryItemMock);

        $inventoryItemMock->expects($this->once())
            ->method('setBackOrdersDatetime')
            ->with(null);

        $inventoryItemMock->expects($this->once())
            ->method('setCanPreorder')
            ->with(false);

        $inventoryItemMock->expects($this->once())
            ->method('setPreOrdersDatetime')
            ->with(null);

        $items[] = ['qty' => 10, 'item' => $purchaseOrderItemMock];

        $this->noteActivityProcessor
            ->expects($this->once())
            ->method('addNote')
            ->with($purchaseOrderMock, $items);

        $options = ['entity' => new PropertyPath('test_entity'), 'is_partial' => true];
        $contextData = ['test_entity' => $purchaseOrderMock];
        $context = new ItemStub($contextData);
        $this->action->initialize($options);
        $this->action->execute($context);
    }

    public function testExecuteActionNoEntityGiven()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid configuration of workflow action, expected entity, none given.');
        $context = array('key' => 'value');
        self::callMethod(
            $this->action,
            'executeAction',
            [$context]
        );
    }
}
