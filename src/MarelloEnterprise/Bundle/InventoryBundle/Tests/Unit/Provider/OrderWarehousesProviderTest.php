<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InventoryBundle\Strategy\WFA\WFAStrategyInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use PHPUnit\Framework\TestCase;

use Marello\Bundle\OrderBundle\Entity\Order;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy;
use Marello\Bundle\RuleBundle\RuleFiltration\RuleFiltrationServiceInterface;
use Marello\Bundle\InventoryBundle\Strategy\WFA\WFAStrategiesRegistry;
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
     * @var AclHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $aclHelper;

    /**
     * @var OrderWarehousesProvider
     */
    protected $orderWarehousesProvider;

    protected function setUp(): void
    {
        $this->strategiesRegistry = $this->createMock(WFAStrategiesRegistry::class);
        $this->rulesFiltrationService = $this->createMock(RuleFiltrationServiceInterface::class);
        $this->wfaRuleRepository = $this->createMock(WFARuleRepository::class);
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->any())
            ->method('getRepository')
            ->with(WFARule::class)
            ->willReturn($this->wfaRuleRepository);
        $this->aclHelper = $this->createMock(AclHelper::class);
        $this->orderWarehousesProvider = new OrderWarehousesProvider(
            $this->strategiesRegistry,
            $this->rulesFiltrationService,
            $this->wfaRuleRepository,
            $this->aclHelper
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
        array $expectedResult
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
                'expectedResult' => [$result2]
            ],
            [
                'firstStrategyResults' => [$result1],
                'secondStrategyResults' => [$result2],
                'callStrategiesTimes' => 2,
                'expectedResult' => [$result2]
            ],
            [
                'firstStrategyResults' => [],
                'secondStrategyResults' => [$result2],
                'callStrategiesTimes' => 2,
                'expectedResult' => [$result2]
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
        $strategy = $this->createMock(WFAStrategyInterface::class);
        $strategy
            ->expects(static::any())
            ->method('getWarehouseResults')
            ->willReturn($results);

        return $strategy;
    }
}
