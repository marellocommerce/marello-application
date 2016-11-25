<?php

namespace Marello\Bundle\CoreBundle\Tests\Functional\Workflow;

use Marello\Bundle\CoreBundle\Workflow\Action\WorkflowTransitAction;

class WorkflowTransitActionTest extends WebTestCase
{

    protected $action;

    protected function setUp()
    {
        $this->initClient();

        $provider = $this->getContainer()->get('oro_workflow.configuration.provider.workflow_config');

        $this->action = new WorkflowTransitAction(new ContextAccessor(), $this->getContainer()->get('doctrine.orm.entity_manager'));
        $dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->disableOriginalConstructor()
            ->getMock();
        $this->action->setDispatcher($dispatcher);

        $reflectionClass = new \ReflectionClass(
            'Oro\Bundle\WorkflowBundle\Configuration\WorkflowConfigurationProvider'
        );

        $reflectionProperty = $reflectionClass->getProperty('configDirectory');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($provider, '/Tests/Functional/Command/DataFixtures/');
    }


}
