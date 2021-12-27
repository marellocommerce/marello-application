<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Provider;

use Symfony\Contracts\Translation\TranslatorInterface;

use PHPUnit\Framework\TestCase;

use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategyInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategiesRegistry;
use MarelloEnterprise\Bundle\InventoryBundle\Provider\WFAStrategyChoicesProvider;

class WFAStrategyChoicesProviderTest extends TestCase
{
    /**
     * @var WFAStrategiesRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $strategiesRegistry;

    /**
     * @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $translator;

    /**
     * @var WFAStrategyChoicesProvider
     */
    protected $wFAStrategyChoicesProvider;

    protected function setUp(): void
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
     * @return \PHPUnit\Framework\MockObject\MockObject
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
