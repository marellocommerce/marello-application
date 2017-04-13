<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Twig;

use Marello\Bundle\OrderBundle\Twig\OrderExtension;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class OrderExtensionTest extends WebTestCase
{
    /**
     * @var WorkflowManager
     */
    protected $workflowManager;

    /**
     * @var OrderExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->workflowManager = $this->client->getKernel()->getContainer()->get('oro_workflow.manager');
        $this->extension = new OrderExtension($this->workflowManager);
    }

    protected function tearDown()
    {
        unset($this->extension);
        unset($this->workflowManager);
    }

    public function testGetName()
    {
        $this->assertEquals(OrderExtension::NAME, $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(1, $functions);

        $expectedFunctions = array(
            'marello_order_can_return'
        );

        /** @var \Twig_SimpleFunction $function */
        foreach ($functions as $function) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $function);
            $this->assertContains($function->getName(), $expectedFunctions);
        }
    }
}
