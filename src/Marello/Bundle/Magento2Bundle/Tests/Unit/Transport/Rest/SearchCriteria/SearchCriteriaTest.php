<?php

namespace Marello\Bundle\Magento2Bundle\Tests\Unit\Transport\Rest\SearchCriteria;

use Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria\Filter;
use Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria\SearchCriteria;
use Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria\SortOrder;
use PHPUnit\Framework\TestCase;

class SearchCriteriaTest extends TestCase
{
    private const PAGE_SIZE = 5;
    private const PAGE_NUMBER = 5;

    /** @var SearchCriteria */
    private $searchCriteria;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->searchCriteria = new SearchCriteria(self::PAGE_SIZE,self::PAGE_NUMBER);
    }

    public function testSearchCriteria()
    {
        $this->assertSame(self::PAGE_SIZE, $this->searchCriteria->getPageSize());
        $this->assertSame(self::PAGE_NUMBER, $this->searchCriteria->getPageNumber());
        $this->assertSame([
            'searchCriteria' => [
                'pageSize' => self::PAGE_SIZE,
                'currentPage' => self::PAGE_NUMBER
            ]
        ], $this->searchCriteria->getSearchCriteriaParams());

        $this->searchCriteria->nextPage();
        $this->assertSame(self::PAGE_SIZE + 1, $this->searchCriteria->getPageNumber());
    }

    public function testSearchCriteriaWithFilterAndOrders()
    {
        $updatedAtFieldName = 'updatedAt';
        $storeFieldName = 'store';
        $this->searchCriteria->addSortOrder(new SortOrder($updatedAtFieldName, SortOrder::DIRECTION_ASC));
        $this->searchCriteria->addFilters(
            new Filter($storeFieldName, Filter::CONDITION_EQ, 1),
            new Filter($storeFieldName, Filter::CONDITION_EQ, 3)
        );
        $this->searchCriteria->addFilters(
            new Filter($updatedAtFieldName, Filter::CONDITION_FROM, '2019-12-31 23:55:00')
        );
        $this->searchCriteria->addFilters(
            new Filter($updatedAtFieldName, Filter::CONDITION_TO, '2020-01-08 00:00:00')
        );

        $this->assertSame([
            'searchCriteria' => [
                'pageSize' => self::PAGE_SIZE,
                'currentPage' => self::PAGE_NUMBER,
                'filterGroups' => [
                    [
                        'filters' => [
                            [
                                'field' => $storeFieldName,
                                'value' => '1',
                                'condition_type' => Filter::CONDITION_EQ
                            ],
                            [
                                'field' => $storeFieldName,
                                'value' => '3',
                                'condition_type' => Filter::CONDITION_EQ
                            ]
                        ]
                    ],
                    [
                        'filters' => [
                            [
                                'field' => $updatedAtFieldName,
                                'value' => '2019-12-31 23:55:00',
                                'condition_type' => Filter::CONDITION_FROM
                            ],
                        ]
                    ],
                    [
                        'filters' => [
                            [
                                'field' => $updatedAtFieldName,
                                'value' => '2020-01-08 00:00:00',
                                'condition_type' => Filter::CONDITION_TO
                            ],
                        ]
                    ],
                ],
                'sortOrders' => [
                    [
                        'field' => $updatedAtFieldName,
                        'direction' => SortOrder::DIRECTION_ASC
                    ]
                ]
            ]
        ], $this->searchCriteria->getSearchCriteriaParams());
    }
}
