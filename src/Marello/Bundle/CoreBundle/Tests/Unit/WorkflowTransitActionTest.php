<?php

namespace Marello\Bundle\CoreBundle\Tests\Unit;

use Marello\Bundle\CoreBundle\Workflow\Action\WorkflowTransitAction;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\ConfigExpression\ContextAccessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyPath;

class WorkflowTransitActionTest extends TestCase
{
    /** @var ContextAccessor|\PHPUnit\Framework\MockObject\MockObject $contextAccessor */
    protected $contextAccessor;

    /** @var WorkflowManager|\PHPUnit\Framework\MockObject\MockObject $workflowManager */
    protected $workflowManager;

    /**
     * @var WorkflowTransitAction
     */
    protected $action;

    protected function setUp(): void
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

    protected function getPropertyPath()
    {
        return $this->getMockBuilder(PropertyPath::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testInitializeExceptionNoWorkflowItem()
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Parameter "transitionName" is required.');
        $this->action->initialize([]);
    }

    public function testInitializeExceptionNoTransitionName()
    {
        $options = [
            'transitionNameInvalidArrayKey' => $this->getPropertyPath(),
        ];

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Parameter "transitionName" is required.');
        $this->action->initialize($options);
    }

    public function testInitialize()
    {
        $options = [
            'transitionName' => 'test_transition'
        ];

        $this->action->initialize($options);
    }
}
