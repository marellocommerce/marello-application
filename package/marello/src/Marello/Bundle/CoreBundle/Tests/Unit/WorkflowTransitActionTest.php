<?php

namespace Marello\Bundle\CoreBundle\Tests\Unit;

use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\EventDispatcher\EventDispatcher;

use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Component\ConfigExpression\Tests\Unit\Fixtures\ItemStub;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use Marello\Bundle\CoreBundle\Workflow\Action\WorkflowTransitAction;

class WorkflowTransitActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContextAccessor|\PHPUnit_Framework_MockObject_MockObject $contextAccessor */
    protected $contextAccessor;

    /** @var WorkflowManager|\PHPUnit_Framework_MockObject_MockObject $workflowManager */
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
     * @expectedExceptionMessage Parameter "transitionName" is required.
     */
    public function testInitializeExceptionNoWorkflowItem()
    {
        $this->action->initialize([]);
    }

    /**
     * @expectedException \Oro\Component\Action\Exception\InvalidParameterException
     * @expectedExceptionMessage Parameter "transitionName" is required.
     */
    public function testInitializeExceptionNoTransitionName()
    {
        $options = [
            'transitionNameInvalidArrayKey'      => $this->getPropertyPath(),
        ];

        $this->action->initialize($options);
    }

    public function testInitialize()
    {
        $options = [
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
            'transitionName'    => 'go_to_next_definition'
        ];

        $contextData = ['test_item' => $workflowItemMock];
        $context = new ItemStub($contextData);

        $this->action->initialize($options);
        $this->action->execute($context);
    }
}
