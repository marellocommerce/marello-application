<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy;

use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategyInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategiesRegistry;

class WFAStrategiesRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WFAStrategiesRegistry
     */
    protected $wFAStrategiesRegistry;

    protected function setUp()
    {
        $this->wFAStrategiesRegistry = new WFAStrategiesRegistry();
    }

    public function testAddStrategy()
    {
        $strategy = $this->mockStrategy('identifier');
        static::assertEquals([], $this->wFAStrategiesRegistry->getStrategies());
        $this->wFAStrategiesRegistry->addStrategy($strategy);
        static::assertEquals(['identifier' => $strategy], $this->wFAStrategiesRegistry->getStrategies());
    }

    public function testGetStrategy()
    {
        $strategy = $this->mockStrategy('identifier');
        $this->wFAStrategiesRegistry->addStrategy($strategy);
        static::assertEquals($strategy, $this->wFAStrategiesRegistry->getStrategy('identifier'));
    }

    public function testGetStrategies()
    {
        $strategy1 = $this->mockStrategy('identifier1');
        $strategy2 = $this->mockStrategy('identifier2');
        static::assertEquals([], $this->wFAStrategiesRegistry->getStrategies());
        $this->wFAStrategiesRegistry
            ->addStrategy($strategy1)
            ->addStrategy($strategy2);
        static::assertEquals(
            ['identifier1' => $strategy1, 'identifier2' => $strategy2],
            $this->wFAStrategiesRegistry->getStrategies()
        );
    }

    public function testHasStrategy()
    {
        $strategy = $this->mockStrategy('identifier');
        static::assertEquals(false, $this->wFAStrategiesRegistry->hasStrategy('identifier'));
        $this->wFAStrategiesRegistry->addStrategy($strategy);
        static::assertEquals(true, $this->wFAStrategiesRegistry->hasStrategy('identifier'));
    }

    /**
     * @param string $identifier
     * @return WFAStrategyInterface|\PHPUnit_Framework_MockObject_MockObject $strategy
     */
    private function mockStrategy($identifier)
    {
        $strategy = $this->createMock(WFAStrategyInterface::class);
        $strategy
            ->expects(static::once())
            ->method('getIdentifier')
            ->willReturn($identifier);

        return $strategy;
    }
}
