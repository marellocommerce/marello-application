<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Provider;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\OrderBundle\Entity\Order;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\MinimumQuantityWFAStrategy;
use Marello\Bundle\RuleBundle\RuleFiltration\RuleFiltrationServiceInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategiesRegistry;
use MarelloEnterprise\Bundle\InventoryBundle\Provider\OrderWarehousesProvider;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WFARuleRepository;

class OrderWarehousesProviderTest extends TestCase
{
    /**
     * @var WFAStrategiesRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $strategiesRegistry;

    /**
     * @var RuleFiltrationServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rulesFiltrationService;

    /**
     * @var WFARuleRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $wfaRuleRepository;

    /**
     * @var OrderWarehousesProvider
     */
    protected $orderWarehousesProvider;

    protected function setUp(): void
    {
        $this->strategiesRegistry = $this->createMock(WFAStrategiesRegistry::class);
        $this->rulesFiltrationService = $this->createMock(RuleFiltrationServiceInterface::class);
        $this->wfaRuleRepository = $this->getMockBuilder(WFARuleRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderWarehousesProvider = new OrderWarehousesProvider(
            $this->strategiesRegistry,
            $this->rulesFiltrationService,
            $this->wfaRuleRepository
        );
    }

    /**
     * @dataProvider getWarehousesForOrderDataProvider
     *
     * @param array $firstStrategyResults
     * @param array $secondStrategyResults
     * @param int $callStrategiesTimes
     * @param OrderWarehouseResult $expectedResult
     */
    public function testGetWarehousesForOrder(
        array $firstStrategyResults,
        array $secondStrategyResults,
        $callStrategiesTimes,
        OrderWarehouseResult $expectedResult
    ) {
        /** @var Order|\PHPUnit\Framework\MockObject\MockObject $order **/
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $wfaRule1 = $this->mockWfaRule('strategy1');
        $wfaRule2 = $this->mockWfaRule('strategy2');

        $this->wfaRuleRepository->expects(static::once())
            ->method('findAllWFARules')
            ->willReturn([$wfaRule1, $wfaRule2]);
        $this->rulesFiltrationService
            ->expects(static::once())
            ->method('getFilteredRuleOwners')
            ->willReturn([$wfaRule1, $wfaRule2]);
        $this->strategiesRegistry
            ->expects(static::exactly($callStrategiesTimes))
            ->method('getStrategy')
            ->withConsecutive(
                ['strategy1'],
                ['strategy2']
            )
            ->willReturnOnConsecutiveCalls(
                $this->mockStrategy($firstStrategyResults),
                $this->mockStrategy($secondStrategyResults)
            );

        static::assertEquals($expectedResult, $this->orderWarehousesProvider->getWarehousesForOrder($order));
    }

    /**
     * @return array
     */
    public function getWarehousesForOrderDataProvider()
    {
        $result1 = new OrderWarehouseResult([OrderWarehouseResult::WAREHOUSE_FIELD => 'warehouse1']);
        $result2 = new OrderWarehouseResult([OrderWarehouseResult::WAREHOUSE_FIELD => 'warehouse2']);
        return [
            [
                'firstStrategyResults' => [$result1, $result2],
                'secondStrategyResults' => [$result2],
                'callStrategiesTimes' => 2,
                'expectedResult' => $result2
            ],
            [
                'firstStrategyResults' => [$result1],
                'secondStrategyResults' => [$result2],
                'callStrategiesTimes' => 2,
                'expectedResult' => $result2
            ],
            [
                'firstStrategyResults' => [],
                'secondStrategyResults' => [$result2],
                'callStrategiesTimes' => 2,
                'expectedResult' => $result2
            ]
        ];
    }

    /**
     * @param string $strategy
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function mockWfaRule($strategy)
    {
        $rule = $this->createMock(WFARule::class);
        $rule
            ->expects(static::any())
            ->method('getStrategy')
            ->willReturn($strategy);

        return $rule;
    }

    /**
     * @param array $results
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function mockStrategy(array $results)
    {
        // temporarily replace WFAStrategyInterface for concrete implementation because of prevention for BC breaks
        // setEstimation method will be added in major version not in 2.2
        $strategy = $this->createMock(MinimumQuantityWFAStrategy::class);
        $strategy
            ->expects(static::any())
            ->method('getWarehouseResults')
            ->willReturn($results);

        return $strategy;
    }
}
