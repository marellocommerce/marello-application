<?php

namespace Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Workflow\Action;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\EventDispatcher\EventDispatcher;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\ConfigExpression\Tests\Unit\Fixtures\ItemStub;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Marello\Bundle\PurchaseOrderBundle\Workflow\Action\TransitCompleteAction;

class TransitCompleteActionTest extends TestCase
{
    /** @var TransitCompleteAction $action */
    protected $action;

    /** @var WorkflowManager $workflowManager */
    protected $workflowManager;

    /** @var ContextAccessor $contextAccessor */
    protected $contextAccessor;

    public function setUp(): void
    {
        $this->contextAccessor = new ContextAccessor();

        $this->workflowManager = $this
            ->getMockBuilder(WorkflowManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var TransitCompleteAction action */
        $this->action = new TransitCompleteAction(
            $this->contextAccessor,
            $this->workflowManager
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
    public function testInitialize()
    {
        $options = [
            'entity' => $this->getPropertyPath(),
            'workflowItem' => $this->getPropertyPath(),
            'transitionName' => $this->getPropertyPath()
        ];
        $this->action->initialize($options);
    }


    public function testExecute()
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

        $purchaseOrderItemMock->expects($this->once())
            ->method('getOrderedAmount')
            ->willReturn(10);

        $purchaseOrderItemMock->expects($this->once())
            ->method('getReceivedAmount')
            ->willReturn(10);

        $purchaseOrderItemMock2->expects($this->once())
            ->method('getOrderedAmount')
            ->willReturn(10);

        $purchaseOrderItemMock2->expects($this->once())
            ->method('getReceivedAmount')
            ->willReturn(10);

        $workflowItemMock = $this
            ->getMockBuilder(WorkflowItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $options = [
            'entity'            => new PropertyPath('test_entity'),
            'workflowItem'      => new PropertyPath('test_item'),
            'transitionName'    => 'transition'
        ];

        $contextData = [
            'test_entity'       => $purchaseOrderMock,
            'test_item'         => $workflowItemMock,
        ];

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
