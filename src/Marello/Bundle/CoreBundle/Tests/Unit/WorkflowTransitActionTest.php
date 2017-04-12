<?php

namespace Marello\Bundle\CoreBundle\Tests\Functional\Workflow;

use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\EventDispatcher\EventDispatcher;

use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Component\ConfigExpression\Tests\Unit\Fixtures\ItemStub;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use Marello\Bundle\CoreBundle\Workflow\Action\WorkflowTransitAction;

class WorkflowTransitActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    protected $contextAccessor;

    protected $workflowManager;

    /**
     * @var WorkflowTransitAction
     */
    protected $action;

    protected function setUp()
    {
        $this->contextAccessor = new ContextAccessor();

        $this->workflowManager = $this
            ->getMockBuilder(WorkflowManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var WorkflowTransitAction action */
        $this->action = new WorkflowTransitAction(
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
     * @expectedExceptionMessage Parameter "workflowItem" is required.
     */
    public function testInitializeExceptionNoWorkflowItem()
    {
        $this->action->initialize([]);
    }

    /**
     * @expectedException \Oro\Component\Action\Exception\InvalidParameterException
     * @expectedExceptionMessage workflowItem must be valid property definition.
     */
    public function testInitializeExceptionWorkflowItemNotValidProperty()
    {
        $this->action->initialize(['workflowItem' => 1]);
    }

    /**
     * @expectedException \Oro\Component\Action\Exception\InvalidParameterException
     * @expectedExceptionMessage Parameter "transitionName" is required.
     */
    public function testInitializeExceptionNoTransitionName()
    {
        $options = [
            'workflowItem'      => $this->getPropertyPath(),
        ];

        $this->action->initialize($options);
    }

    public function testInitialize()
    {
        $options = [
            'workflowItem' => $this->getPropertyPath(),
            'transitionName' => 'test_transition'
        ];

        $this->action->initialize($options);
        $this->assertAttributeEquals($options, 'options', $this->action);
    }

    public function testExecute()
    {
        $workflowItemMock = $this
            ->getMockBuilder(WorkflowItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $options = [
            'workflowItem'      => new PropertyPath('test_item'),
            'transitionName'    => 'go_to_next_definition'
        ];

        $contextData = ['test_item' => $workflowItemMock];
        $context = new ItemStub($contextData);

        $this->action->initialize($options);
        $this->action->execute($context);
    }
}
