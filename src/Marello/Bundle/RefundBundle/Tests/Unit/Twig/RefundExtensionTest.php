<?php

namespace Marello\Bundle\RefundBundle\Tests\Unit\Twig;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

use Marello\Bundle\RefundBundle\Twig\RefundExtension;
use Marello\Bundle\RefundBundle\Entity\Refund;

class RefundExtensionTest extends WebTestCase
{
    /**
     * @var WorkflowManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflowManager;

    /**
     * @var RefundExtension
     */
    protected $extension;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->workflowManager = $this->getMockBuilder(WorkflowManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new RefundExtension($this->workflowManager);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->extension);
        unset($this->workflowManager);
    }

    /**
     * {@inheritdoc}
     */
    public function testNameIsCorrectlySetAndReturnedFromConstant()
    {
        $this->assertEquals(RefundExtension::NAME, $this->extension->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function testGetFunctionsAreRegisteredInExtension()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(1, $functions);

        $expectedFunctions = array(
            'marello_refund_is_pending'
        );

        /** @var \Twig_SimpleFunction $function */
        foreach ($functions as $function) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $function);
            $this->assertContains($function->getName(), $expectedFunctions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function testRefundIsPendingIsFalse()
    {
        /** @var Refund $refund */
        $refund = $this->getMockBuilder(Refund::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->workflowManager
            ->expects($this->once())
            ->method('getWorkflowItemsByEntity')
            ->willReturn([]);

        $this->assertFalse($this->extension->isPending($refund));
    }

    /**
     * {@inheritdoc}
     */
    public function testRefundIsPendingIsTrue()
    {
        /** @var Refund $refund */
        $refund = $this->getMockBuilder(Refund::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var WorkflowItem $workflowItem */
        $workflowItem = $this->getMockBuilder(WorkflowItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var WorkflowStep $workflowStep */
        $workflowStep = $this->getMockBuilder(WorkflowStep::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->workflowManager
            ->expects($this->once())
            ->method('getWorkflowItemsByEntity')
            ->willReturn([$workflowItem]);

        $workflowItem->expects($this->atLeastOnce())
            ->method('getCurrentStep')
            ->willReturn($workflowStep);

        $workflowStep->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('pending');

        $this->assertTrue($this->extension->isPending($refund));
    }
}
