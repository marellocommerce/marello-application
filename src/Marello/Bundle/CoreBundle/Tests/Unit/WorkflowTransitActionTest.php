<?php

namespace Marello\Bundle\CoreBundle\Tests\Functional\Workflow;

use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\EventDispatcher\EventDispatcher;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Component\Action\Action\AssignActiveUser;
use Oro\Component\Action\Model\ContextAccessor;
use Oro\Component\ConfigExpression\Tests\Unit\Fixtures\ItemStub;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

use Marello\Bundle\CoreBundle\Workflow\Action\WorkflowTransitAction;

class WorkflowTransitActionTest extends WebTestCase
{
    const ATTRIBUTE_NAME = 'some_attribute';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $om;

    protected $workflowItem;

    /**
     * @var WorkflowTransitAction
     */
    protected $action;

    protected function setUp()
    {
        $this->om = $this
            ->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->workflowItem = $this
            ->getMockBuilder(WorkflowItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->action = new WorkflowTransitAction(new ContextAccessor(), $this->om);
        $dispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();


        $this->action->setDispatcher($dispatcher);
    }


    public function testInitialize()
    {
        $options = [
            'workflowItem' => $this->workflowItem,
            'transitionName' => 'complete'
        ];

        $this->action->initialize($options);
        $this->assertAttributeEquals($options['workflowItem'], 'workflowItem', $this->action);
        $this->assertAttributeEquals($options['transitionName'], 'transitionName', $this->action);
    }

    protected function tearDown()
    {
        unset($this->om);
        unset($this->action);
        unset($this->workflowItem);
    }

    /**
     * @param array $inputOptions
     * @dataProvider optionsDataProvider
     */
    public function testExecute(array $inputOptions)
    {
        $user = new User('testUser', 'qwerty');
//
//        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
//            ->disableOriginalConstructor()
//            ->getMock();
//        $token->expects($this->once())
//            ->method('getUser')
//            ->will($this->returnValue($user));
//
//        $this->securityContext->expects($this->once())
//            ->method('getToken')
//            ->will($this->returnValue($token));
//
        $context = new ItemStub();

        $this->action->initialize($inputOptions);
        $this->action->execute($context);

        $attributeName = self::ATTRIBUTE_NAME;
        $this->assertEquals($user, $context->$attributeName);
    }
}
