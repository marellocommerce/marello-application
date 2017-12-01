<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Strategy;

use Marello\Bundle\InventoryBundle\Strategy\BalancerStrategyInterface;
use Marello\Bundle\InventoryBundle\Strategy\BalancerStrategiesRegistry;

class BalancerStrategiesRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BalancerStrategiesRegistry
     */
    protected $balancerStrategiesRegistry;

    protected function setUp()
    {
        $this->balancerStrategiesRegistry = new BalancerStrategiesRegistry();
    }

    /**
     * {@inheritdoc}
     */
    public function testAddStrategy()
    {
        $strategy = $this->mockStrategy('identifier');
        static::assertEquals([], $this->balancerStrategiesRegistry->getStrategies());
        $this->balancerStrategiesRegistry->addStrategy($strategy);
        static::assertEquals(['identifier' => $strategy], $this->balancerStrategiesRegistry->getStrategies());
    }

    /**
     * {@inheritdoc}
     */
    public function testGetStrategy()
    {
        $strategy = $this->mockStrategy('identifier');
        $this->balancerStrategiesRegistry->addStrategy($strategy);
        static::assertEquals($strategy, $this->balancerStrategiesRegistry->getStrategy('identifier'));
    }

    /**
     * {@inheritdoc}
     */
    public function testGetStrategies()
    {
        $strategy1 = $this->mockStrategy('identifier1');
        $strategy2 = $this->mockStrategy('identifier2');
        static::assertEquals([], $this->balancerStrategiesRegistry->getStrategies());
        $this->balancerStrategiesRegistry
            ->addStrategy($strategy1)
            ->addStrategy($strategy2);
        static::assertEquals(
            ['identifier1' => $strategy1, 'identifier2' => $strategy2],
            $this->balancerStrategiesRegistry->getStrategies()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function testHasStrategy()
    {
        $strategy = $this->mockStrategy('identifier');
        static::assertEquals(false, $this->balancerStrategiesRegistry->hasStrategy('identifier'));
        $this->balancerStrategiesRegistry->addStrategy($strategy);
        static::assertEquals(true, $this->balancerStrategiesRegistry->hasStrategy('identifier'));
    }

    /**
     * @param string $identifier
     * @return BalancerStrategyInterface|\PHPUnit_Framework_MockObject_MockObject $strategy
     */
    private function mockStrategy($identifier)
    {
        $strategy = $this->createMock(BalancerStrategyInterface::class);
        $strategy
            ->expects(static::once())
            ->method('getIdentifier')
            ->willReturn($identifier);

        return $strategy;
    }
}
