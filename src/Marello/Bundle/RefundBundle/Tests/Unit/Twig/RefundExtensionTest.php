<?php

namespace Marello\Bundle\RefundBundle\Tests\Unit\Twig;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use Marello\Bundle\RefundBundle\Twig\RefundExtension;

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
}
