<?php

namespace Marello\Bundle\RefundBundle\Tests\Unit\Twig;

use Marello\Bundle\RefundBundle\Twig\RefundExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class RefundExtensionTest extends WebTestCase
{
    /**
     * @var WorkflowManager
     */
    protected $workflowManager;

    /**
     * @var RefundExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->workflowManager = $this->client->getKernel()->getContainer()->get('oro_workflow.manager');
        $this->extension = new RefundExtension($this->workflowManager);
    }

    protected function tearDown()
    {
        unset($this->extension);
        unset($this->workflowManager);
    }

    public function testGetName()
    {
        $this->assertEquals(RefundExtension::NAME, $this->extension->getName());
    }

    public function testGetFunctions()
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
