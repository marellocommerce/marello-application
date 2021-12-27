<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Workflow\Condition;

use Marello\Bundle\PdfBundle\Workflow\Condition\IsSendEmailTransition;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyPath;

class IsSendEmailTransitionTest extends TestCase
{
    use EntityTrait;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $configManager;

    protected $isSendTransition;

    protected $salesChannel;

    public function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->isSendTransition = new IsSendEmailTransition($this->configManager);
        $this->isSendTransition->setContextAccessor(new ContextAccessor());
    }

    /**
     * @dataProvider isConditionAllowedProvider
     */
    public function testIsConditionAllowed($configTransition, $context, $options, $salesChannel, $allowed)
    {
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('marello_pdf.email_workflow_transition', false, false, $salesChannel)
            ->willReturn($configTransition)
        ;

        $this->isSendTransition->initialize($options);

        $this->assertEquals($allowed, $this->isSendTransition->isConditionAllowed($context));
    }

    public function isConditionAllowedProvider()
    {
        $salesChannel = $this->getEntity(SalesChannel::class, [
            'id' => 1,
            'name' => 'test channel',
            'code' => 'test',
        ]);

        $options = [
            IsSendEmailTransition::OPTION_CURRENT_TRANSITION => 'provided',
            IsSendEmailTransition::OPTION_CONFIG_SCOPE => new PropertyPath('scope'),
        ];

        $context = [
            'scope' => $salesChannel,
        ];

        return [
            'not_set' => [
                'configTransition' => null,
                'context' => $context,
                'options' => $options,
                'salesChannel' => $salesChannel,
                'allowed' => false,
            ],
            'allowed' => [
                'configTransition' => 'provided',
                'context' => $context,
                'options' => $options,
                'salesChannel' => $salesChannel,
                'allowed' => true,
            ],
            'not_allowed' => [
                'configTransition' => 'config',
                'context' => $context,
                'options' => $options,
                'salesChannel' => $salesChannel,
                'allowed' => false,
            ],
        ];
    }

    public function testGetName()
    {
        $this->assertEquals(IsSendEmailTransition::NAME, $this->isSendTransition->getName());
    }
}
