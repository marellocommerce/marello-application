<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Provider;

use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\RuleBundle\RuleFiltration\RuleFiltrationServiceInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WFARuleRepository;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use MarelloEnterprise\Bundle\InventoryBundle\Provider\OrderWarehousesProvider;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategiesRegistry;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategyInterface;

class OrderWarehousesProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WFAStrategiesRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $strategiesRegistry;

    /**
     * @var RuleFiltrationServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rulesFiltrationService;

    /**
     * @var WFARuleRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $wfaRuleRepository;

    /**
     * @var OrderWarehousesProvider
     */
    protected $orderWarehousesProvider;

    protected function setUp()
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
        /** @var Order|\PHPUnit_Framework_MockObject_MockObject $order **/
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
                'callStrategiesTimes' => 1,
                'expectedResult' => $result1
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
     * @return \PHPUnit_Framework_MockObject_MockObject
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockStrategy(array $results)
    {
        $strategy = $this->createMock(WFAStrategyInterface::class);
        $strategy
            ->expects(static::any())
            ->method('getWarehouseResults')
            ->willReturn($results);

        return $strategy;
    }
}
