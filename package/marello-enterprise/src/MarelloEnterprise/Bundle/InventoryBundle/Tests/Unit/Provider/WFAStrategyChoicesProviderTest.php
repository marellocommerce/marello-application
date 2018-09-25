<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Provider;

use MarelloEnterprise\Bundle\InventoryBundle\Provider\WFAStrategyChoicesProvider;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategiesRegistry;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategyInterface;
use Symfony\Component\Translation\TranslatorInterface;

class WFAStrategyChoicesProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WFAStrategiesRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $strategiesRegistry;

    /**
     * @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translator;

    /**
     * @var WFAStrategyChoicesProvider
     */
    protected $wFAStrategyChoicesProvider;

    protected function setUp()
    {
        $this->strategiesRegistry = $this->createMock(WFAStrategiesRegistry::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->wFAStrategyChoicesProvider = new WFAStrategyChoicesProvider(
            $this->strategiesRegistry,
            $this->translator
        );
    }

    public function testGetChoices()
    {
        $strategy1 = $this->mockStrategy('identifier1', 'label1');
        $strategy2 = $this->mockStrategy('identifier2', 'label2');
        $this->strategiesRegistry
            ->expects(static::once())
            ->method('getStrategies')
            ->willReturn([$strategy1, $strategy2]);

        $this->translator
            ->expects(static::exactly(2))
            ->method('trans')
            ->withConsecutive(
                ['label1'],
                ['label2']
            )
            ->willReturnOnConsecutiveCalls(
                'label1',
                'label2'
            );
        $expectedResults = [
            'identifier1' => 'label1',
            'identifier2' => 'label2',
        ];
        $actualResults = $this->wFAStrategyChoicesProvider->getChoices();

        static::assertEquals($expectedResults, $actualResults);
    }

    /**
     * @param string $identifier
     * @param string $label
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockStrategy($identifier, $label)
    {
        $strategy = $this->createMock(WFAStrategyInterface::class);
        $strategy
            ->expects(static::once())
            ->method('isEnabled')
            ->willReturn(true);
        $strategy
            ->expects(static::once())
            ->method('getIdentifier')
            ->willReturn($identifier);
        $strategy
            ->expects(static::once())
            ->method('getLabel')
            ->willReturn($label);

        return $strategy;
    }
}
