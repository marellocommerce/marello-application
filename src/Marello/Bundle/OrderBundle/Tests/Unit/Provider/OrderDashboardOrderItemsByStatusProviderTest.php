<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Provider;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;
use Marello\Bundle\OrderBundle\Provider\OrderDashboardOrderItemsByStatusProvider;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilterManager;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Bridge\Doctrine\RegistryInterface;
use PHPUnit\Framework\TestCase;

class OrderDashboardOrderItemsByStatusProviderTest extends TestCase
{
    /** @var RegistryInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $registry;

    /** @var AclHelper|\PHPUnit\Framework\MockObject\MockObject */
    protected $aclHelper;

    /** @var WidgetProviderFilterManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $widgetProviderFilter;

    /** @var DateFilterProcessor|\PHPUnit\Framework\MockObject\MockObject */
    protected $dateFilterProcessor;

    /** @var  CurrencyQueryBuilderTransformerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $qbTransformer;

    protected $opportunityStatuses = [
        ['id' => 'won', 'name' => 'Won'],
        ['id' => 'identification_alignment', 'name' => 'Identification'],
        ['id' => 'in_progress', 'name' => 'Open'],
        ['id' => 'needs_analysis', 'name' => 'Analysis'],
        ['id' => 'negotiation', 'name' => 'Negotiation'],
        ['id' => 'solution_development', 'name' => 'Development'],
        ['id' => 'lost', 'name' => 'Lost']
    ];

    /** @var  OrderDashboardOrderItemsByStatusProvider */
    protected $provider;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->registry = $this->createMock(RegistryInterface::class);
        $this->aclHelper = $this->createMock(AclHelper::class);
        $this->widgetProviderFilter = $this->createMock(WidgetProviderFilterManager::class);
        $this->dateFilterProcessor = $this->createMock(DateFilterProcessor::class);
        $this->qbTransformer = $this->createMock(CurrencyQueryBuilderTransformerInterface::class);
        $this->provider = new OrderDashboardOrderItemsByStatusProvider(
            $this->registry,
            $this->aclHelper,
            $this->widgetProviderFilter,
            $this->dateFilterProcessor,
            $this->qbTransformer
        );
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @param string          $expectation
     *
     * @dataProvider getOrderItemsGroupedByStatusDQLDataProvider
     */
    public function testgetOrderItemsGroupedByStatusDQL($widgetOptions, $expectation)
    {
        $opportunityQB = new QueryBuilder($this->getMockForAbstractClass('Doctrine\ORM\EntityManagerInterface'));
        $opportunityQB
            ->from('MarelloOrderBundle:Order', 'o', null);

        $statusesQB = $this->getMockQueryBuilder();
        $statusesQB->expects($this->once())
            ->method('select')
            ->with('s.id, s.name')
            ->willReturnSelf();
        $statusesQB->expects($this->once())
            ->method('getQuery')
            ->willReturnSelf();
        $statusesQB->expects($this->once())
            ->method('getArrayResult')
            ->willReturn($this->opportunityStatuses);

        $repository = $this->getMockRepository();
        $repository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->withConsecutive(['o'], ['s'])
            ->willReturnOnConsecutiveCalls($opportunityQB, $statusesQB);

        $this->registry->expects($this->exactly(2))
            ->method('getRepository')
            ->withConsecutive(
                ['MarelloOrderBundle:Order'],
                [ExtendHelper::buildEnumValueClassName(LoadOrderItemStatusData::ITEM_STATUS_ENUM_CLASS)]
            )
            ->willReturn($repository);

        $mockResult = $this->getMockQueryBuilder();
        $mockResult->expects($this->once())
            ->method('getArrayResult')
            ->willReturn([]);

        $self = $this;
        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->with(
                $this->callback(function ($query) use ($self, $expectation) {
                    /** @var Query $query */
                    $self->assertEquals($expectation, $query->getDQL());

                    return true;
                })
            )
            ->willReturn($mockResult);

        $this->provider->getOrderItemsGroupedByStatus($widgetOptions);
    }

    public function getOrderItemsGroupedByStatusDQLDataProvider()
    {
        return [
            'request quantities'                                                    => [
                'widgetOptions' => new WidgetOptionBag([
                    'statuses' => [],
                    'useQuantityAsData' => true
                ]),
                'expected DQL'  =>
                    'SELECT IDENTITY (oi.status) status, COUNT(oi.id) as quantity '
                    . 'FROM MarelloOrderBundle:Order o '
                    . 'INNER JOIN o.items oi '
                    . 'WHERE IDENTITY (oi.status) IS NOT NULL '
                    . 'GROUP BY oi.status '
                    . 'ORDER BY quantity DESC'
            ],
            'request quantities with excluded statuses - should not affect DQL'     => [
                'widgetOptions' => new WidgetOptionBag([
                    'statuses' => ['in_progress', 'won'],
                    'useQuantityAsData' => true
                ]),
                'expected DQL'  =>
                    'SELECT IDENTITY (oi.status) status, COUNT(oi.id) as quantity '
                    . 'FROM MarelloOrderBundle:Order o '
                    . 'INNER JOIN o.items oi '
                    . 'WHERE IDENTITY (oi.status) IS NOT NULL '
                    . 'GROUP BY oi.status '
                    . 'ORDER BY quantity DESC'
            ],
        ];
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @param array           $result
     * @param string          $expected
     *
     * @dataProvider getOrderItemsGroupedByStatusResultDataProvider
     */
    public function testgetOrderItemsGroupedByStatusResultFormatter($widgetOptions, $result, $expected)
    {
        $opportunityQB = new QueryBuilder($this->createMock('Doctrine\ORM\EntityManagerInterface'));
        $opportunityQB
            ->from('Oro\Bundle\SalesBundle\Entity\Opportunity', 'o', null);

        $statusesQB = $this->getMockQueryBuilder();
        $statusesQB->expects($this->once())
            ->method('select')
            ->with('s.id, s.name')
            ->willReturnSelf();
        $statusesQB->expects($this->once())
            ->method('getQuery')
            ->willReturnSelf();
        $statusesQB->expects($this->once())
            ->method('getArrayResult')
            ->willReturn($this->opportunityStatuses);

        $repository = $this->getMockRepository();
        $repository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->withConsecutive(['o'], ['s'])
            ->willReturnOnConsecutiveCalls($opportunityQB, $statusesQB);

        $this->registry->expects($this->exactly(2))
            ->method('getRepository')
            ->withConsecutive(
                ['MarelloOrderBundle:Order'],
                [ExtendHelper::buildEnumValueClassName(LoadOrderItemStatusData::ITEM_STATUS_ENUM_CLASS)]
            )
            ->willReturn($repository);

        $mockResult = $this->getMockQueryBuilder();
        $mockResult->expects($this->once())
            ->method('getArrayResult')
            ->willReturn($result);

        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->willReturn($mockResult);

        $data = $this->provider->getOrderItemsGroupedByStatus($widgetOptions);

        $this->assertEquals($expected, $data);
    }

    public function getOrderItemsGroupedByStatusResultDataProvider()
    {
        return [
            'result with all statuses, no exclusions - only labels should be added'               => [
                'widgetOptions'             => new WidgetOptionBag([
                    'statuses' => [],
                    'useQuantityAsData' => true
                ]),
                'result data'               => [
                    0 => ['quantity' => 700, 'status' => 'won'],
                    1 => ['quantity' => 600, 'status' => 'identification_alignment'],
                    2 => ['quantity' => 500, 'status' => 'in_progress'],
                    3 => ['quantity' => 400, 'status' => 'needs_analysis'],
                    4 => ['quantity' => 300, 'status' => 'negotiation'],
                    5 => ['quantity' => 200, 'status' => 'solution_development'],
                    6 => ['quantity' => 100, 'status' => 'lost'],
                ],
                'expected formatted result' => [
                    0 => ['quantity' => 700, 'status' => 'won', 'label' => 'Won'],
                    1 => ['quantity' => 600, 'status' => 'identification_alignment', 'label' => 'Identification'],
                    2 => ['quantity' => 500, 'status' => 'in_progress', 'label' => 'Open'],
                    3 => ['quantity' => 400, 'status' => 'needs_analysis', 'label' => 'Analysis'],
                    4 => ['quantity' => 300, 'status' => 'negotiation', 'label' => 'Negotiation'],
                    5 => ['quantity' => 200, 'status' => 'solution_development', 'label' => 'Development'],
                    6 => ['quantity' => 100, 'status' => 'lost', 'label' => 'Lost'],
                ]
            ],
            'result with all statuses, with exclusions - excluded should be removed, labels'      => [
                'widgetOptions'             => new WidgetOptionBag([
                    'statuses' => ['identification_alignment', 'solution_development'],
                    'useQuantityAsData' => true
                ]),
                'result data'               => [
                    0 => ['quantity' => 700, 'status' => 'won'],
                    1 => ['quantity' => 600, 'status' => 'identification_alignment'],
                    2 => ['quantity' => 500, 'status' => 'in_progress'],
                    3 => ['quantity' => 400, 'status' => 'needs_analysis'],
                    4 => ['quantity' => 300, 'status' => 'negotiation'],
                    5 => ['quantity' => 200, 'status' => 'solution_development'],
                    6 => ['quantity' => 100, 'status' => 'lost'],
                ],
                'expected formatted result' => [
                    1 => ['quantity' => 600, 'status' => 'identification_alignment', 'label' => 'Identification'],
                    5 => ['quantity' => 200, 'status' => 'solution_development', 'label' => 'Development'],
                ]
            ],
            'result with NOT all statuses, no exclusions - all statuses, labels'                  => [
                'widgetOptions'             => new WidgetOptionBag([
                    'statuses' => [],
                    'useQuantityAsData' => true
                ]),
                'result data'               => [
                    0 => ['quantity' => 700, 'status' => 'won'],
                    1 => ['quantity' => 300, 'status' => 'negotiation'],
                ],
                'expected formatted result' => [
                    0 => ['quantity' => 700, 'status' => 'won', 'label' => 'Won'],
                    1 => ['quantity' => 300, 'status' => 'negotiation', 'label' => 'Negotiation'],
                    2 => ['quantity' => 0, 'status' => 'identification_alignment', 'label' => 'Identification'],
                    3 => ['quantity' => 0, 'status' => 'in_progress', 'label' => 'Open'],
                    4 => ['quantity' => 0, 'status' => 'needs_analysis', 'label' => 'Analysis'],
                    5 => ['quantity' => 0, 'status' => 'solution_development', 'label' => 'Development'],
                    6 => ['quantity' => 0, 'status' => 'lost', 'label' => 'Lost'],
                ]
            ],
            'result with NOT all statuses AND exclusions - all statuses(except excluded), labels' => [
                'widgetOptions'             => new WidgetOptionBag([
                    'statuses' => ['identification_alignment', 'lost', 'in_progress'],
                    'useQuantityAsData' => true
                ]),
                'result data'               => [
                    0 => ['quantity' => 700, 'status' => 'won'],
                    1 => ['quantity' => 500, 'status' => 'in_progress'],
                    2 => ['quantity' => 300, 'status' => 'negotiation'],
                    3 => ['quantity' => 100, 'status' => 'lost'],
                ],
                'expected formatted result' => [
                    4 => ['quantity' => 0, 'status' => 'identification_alignment', 'label' => 'Identification'],
                    1 => ['quantity' => 500, 'status' => 'in_progress', 'label' => 'Open'],
                    3 => ['quantity' => 100, 'status' => 'lost', 'label' => 'Lost'],
                ]
            ],
        ];
    }

    /**
     * @return EntityRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMockRepository()
    {
        return $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(['createQueryBuilder'])
            ->getMockForAbstractClass();
    }

    /**
     * @return QueryBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMockQueryBuilder()
    {
        return $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['select', 'where', 'setParameter', 'getQuery', 'getArrayResult'])
            ->getMockForAbstractClass();
    }
}
