<?php

namespace Marello\Bundle\RefundBundle\Tests\Unit\Twig;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

use Marello\Bundle\RefundBundle\Twig\RefundExtension;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator;

class RefundExtensionTest extends WebTestCase
{
    /**
     * @var WorkflowManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $workflowManager;

    /**
     * @var RefundExtension
     */
    protected $extension;

    /** @var RefundBalanceCalculator|\PHPUnit\Framework\MockObject\MockObject */
    protected $refundCalculator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->workflowManager = $this->getMockBuilder(WorkflowManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->refundCalculator = $this->getMockBuilder(RefundBalanceCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension = new RefundExtension(
            $this->workflowManager,
            $this->refundCalculator
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset($this->extension);
        unset($this->workflowManager);
        unset($this->refundCalculator);
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
        $this->assertCount(2, $functions);

        $expectedFunctions = array(
            'marello_refund_is_pending',
            'marello_refund_get_balance'
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
