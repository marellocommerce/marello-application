<?php

namespace Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Workflow\Action;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\EventDispatcher\EventDispatcher;

use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Component\ConfigExpression\Tests\Unit\Fixtures\ItemStub;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

use Marello\Bundle\PurchaseOrderBundle\Workflow\Action\TransitCompleteAction;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;

class TransitCompleteActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var TransitCompleteAction $action */
    protected $action;

    /** @var WorkflowManager $workflowManager */
    protected $workflowManager;

    /** @var ContextAccessor $contextAccessor */
    protected $contextAccessor;

    public function setUp()
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
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    /**
     * Get Symfony PropertyPath
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPropertyPath()
    {
        return $this->getMockBuilder(PropertyPath::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @expectedException \Oro\Component\Action\Exception\InvalidParameterException
     * @expectedExceptionMessage Parameter "entity" is required.
     */
    public function testInitializeExceptionNoEntityObject()
    {
        $this->action->initialize([]);
    }

    /**
     * @expectedException \Oro\Component\Action\Exception\InvalidParameterException
     * @expectedExceptionMessage Entity must be valid property definition.
     */
    public function testInitializeExceptionEntityNotValidProperty()
    {
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
        $this->assertAttributeEquals($options, 'options', $this->action);
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


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid configuration of workflow action, expected entity, none given.
     */
    public function testExecuteActionNoEntityGiven()
    {
        $context = array('key' => 'value');
        self::callMethod(
            $this->action,
            'executeAction',
            [$context]
        );
    }
}
