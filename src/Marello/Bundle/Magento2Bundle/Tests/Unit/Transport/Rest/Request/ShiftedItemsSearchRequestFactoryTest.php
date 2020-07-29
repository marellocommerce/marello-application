<?php

namespace Marello\Bundle\Magento2Bundle\Tests\Unit\Transport\Rest\Request;

use Marello\Bundle\Magento2Bundle\Transport\Rest\Request\SearchRequest;
use Marello\Bundle\Magento2Bundle\Transport\Rest\Request\ShiftedItemsSearchRequestFactory;
use Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria\SearchCriteria;
use PHPUnit\Framework\TestCase;

class ShiftedItemsSearchRequestFactoryTest extends TestCase
{
    /** @var ShiftedItemsSearchRequestFactory */
    private $factory;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->factory = new ShiftedItemsSearchRequestFactory();
    }

    /**
     * @dataProvider getSearchRequestForPreviousPage
     *
     * @param int $countOfShiftedElements
     * @param int $pageSize
     * @param int $currentPageNumber
     * @param int $expectedPageSize
     * @param int $expectedPageNumber
     */
    public function testGetSearchRequestForPreviousPage(
        int $countOfShiftedElements,
        int $pageSize,
        int $currentPageNumber,
        int $expectedPageSize,
        int $expectedPageNumber
    ) {
        $searchRequest = (new SearchRequest('http://some_urn', []))
            ->setSearchCriteria(
                new SearchCriteria($pageSize, $currentPageNumber)
            );

        $newSearchRequest = $this->factory->getSearchRequestForPreviousPage(
            $searchRequest,
            $countOfShiftedElements
        );

        self::assertEquals(
            $expectedPageSize,
            $newSearchRequest->getSearchCriteria()->getPageSize(),
            'Incorrect page size.'
        );

        self::assertEquals(
            $expectedPageNumber,
            $newSearchRequest->getSearchCriteria()->getPageNumber(),
            'Incorrect page number.'
        );
    }

    /**
     * @return array|\int[][]
     */
    public function getSearchRequestForPreviousPage(): array
    {
        return [
            'Case: 1. Check that we load only 1 shifted elements' => [
                'countOfShiftedElements' => 1,
                'pageSize' => 10,
                'currentPageNumber' => 23,
                'expectedPageSize' => 1,
                'expectedPageNumber' => 220
            ],
            'Case: 2. Check that we find right closest divider' => [
                'countOfShiftedElements' => 3,
                'pageSize' => 25,
                'currentPageNumber' => 12,
                'expectedPageSize' => 5,
                'expectedPageNumber' => 55
            ],
            'Case: 3. Check that pageSize equals to shifted elements' => [
                'countOfShiftedElements' => 5,
                'pageSize' => 10,
                'currentPageNumber' => 18,
                'expectedPageSize' => 5,
                'expectedPageNumber' => 34
            ],
            'Case: 4. Check that we correctly process case when number of shifted elements bigger than page size' => [
                'countOfShiftedElements' => 12,
                'pageSize' => 10,
                'currentPageNumber' => 8,
                'expectedPageSize' => 10,
                'expectedPageNumber' => 7
            ],
        ];
    }
}
