<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\MinimumDistance;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\OrderBundle\Entity\Order;
use MarelloEnterprise\Bundle\AddressBundle\Distance\AddressesDistanceCalculatorInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumDistance\MinimumDistanceWFAStrategy;

class MinimumDistanceWFAStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressesDistanceCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $distanceCalculator;

    /**
     * @var MinimumDistanceWFAStrategy
     */
    protected $minimumDistanceWFAStrategy;

    protected function setUp()
    {
        $this->distanceCalculator = $this->createMock(AddressesDistanceCalculatorInterface::class);
        $this->minimumDistanceWFAStrategy = new MinimumDistanceWFAStrategy($this->distanceCalculator);
    }

    public function testGetIdentifier()
    {
        static::assertEquals(
            MinimumDistanceWFAStrategy::IDENTIFIER,
            $this->minimumDistanceWFAStrategy->getIdentifier()
        );
    }

    public function testGetLabel()
    {
        static::assertEquals(
            MinimumDistanceWFAStrategy::LABEL,
            $this->minimumDistanceWFAStrategy->getLabel()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function testIsEnabled()
    {
        static::assertEquals(true, $this->minimumDistanceWFAStrategy->isEnabled());
    }

    /**
     * @dataProvider getWarehouseResultsDataProvider
     *
     * @param array $distances
     * @param int $resultIndex
     */
    public function testGetWarehouseResults(array $distances, $resultIndex)
    {
        $destinationAddress = $this->createMock(MarelloAddress::class);

        $whAddress1 = $this->createMock(MarelloAddress::class);
        $whAddress2 = $this->createMock(MarelloAddress::class);
        $whAddress3 = $this->createMock(MarelloAddress::class);

        $warehouse1 = $this->mockWarehouse($whAddress1);
        $warehouse2 = $this->mockWarehouse($whAddress2);
        $warehouse3 = $this->mockWarehouse($whAddress3);
        /** @var Order|\PHPUnit_Framework_MockObject_MockObject $order **/
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects(static::once())
            ->method('getShippingAddress')
            ->willReturn($destinationAddress);

        $initialResults = [
            [
                new OrderWarehouseResult([
                    OrderWarehouseResult::WAREHOUSE_FIELD => $warehouse1,
                    OrderWarehouseResult::ORDER_ITEMS_FIELD => ['item1']
                ]),
                new OrderWarehouseResult([
                    OrderWarehouseResult::WAREHOUSE_FIELD => $warehouse2,
                    OrderWarehouseResult::ORDER_ITEMS_FIELD => ['item2', 'item3']
                ])
            ],
            [
                new OrderWarehouseResult([
                    OrderWarehouseResult::WAREHOUSE_FIELD => $warehouse3,
                    OrderWarehouseResult::ORDER_ITEMS_FIELD => ['item1', 'item2']
                ]),
                new OrderWarehouseResult([
                    OrderWarehouseResult::WAREHOUSE_FIELD => $warehouse2,
                    OrderWarehouseResult::ORDER_ITEMS_FIELD => ['item3']
                ])
            ]
        ];

        $this->distanceCalculator
            ->expects(static::exactly(4))
            ->method('calculate')
            ->withConsecutive(
                [$whAddress1, $destinationAddress],
                [$whAddress2, $destinationAddress],
                [$whAddress3, $destinationAddress],
                [$whAddress2, $destinationAddress]
            )
            ->willReturnOnConsecutiveCalls(
                $distances[0],
                $distances[1],
                $distances[2],
                $distances[1]
            );

        static::assertEquals(
            [$initialResults[$resultIndex]],
            $this->minimumDistanceWFAStrategy->getWarehouseResults($order, $initialResults)
        );
    }

    /**
     * @return array
     */
    public function getWarehouseResultsDataProvider()
    {
        return [
            [
                'distances' => [10, 20, 30],
                'resultIndex' => 0
            ],
            [
                'distances' => [10, 20, 5],
                'resultIndex' => 1
            ]
        ];
    }

    /**
     * @param MarelloAddress|\PHPUnit_Framework_MockObject_MockObject $address
     * @return Warehouse|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockWarehouse($address)
    {
        $warehouse = $this->createMock(Warehouse::class);
        $warehouse->expects(static::any())
            ->method('getAddress')
            ->willReturn($address);

        return $warehouse;
    }
}
