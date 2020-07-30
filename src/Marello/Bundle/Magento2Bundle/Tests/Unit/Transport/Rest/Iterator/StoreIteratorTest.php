<?php

namespace Marello\Bundle\Magento2Bundle\Tests\Unit\Transport\Rest\Iterator;

use Marello\Bundle\Magento2Bundle\Transport\Rest\Iterator\StoreIterator;
use PHPUnit\Framework\TestCase;

class StoreIteratorTest extends TestCase
{
    /**
     * @dataProvider iterationDataProvider
     *
     * @param array $storeData
     * @param array $storeConfigData
     * @param array $expectedResult
     */
    public function testIteration(
        array $storeData,
        array $storeConfigData,
        array $expectedResult
    ) {
        $iterator = new StoreIterator($storeData, $storeConfigData);

        self::assertSame($expectedResult, \iterator_to_array($iterator));
    }

    /**
     * @return array|\array[][]
     */
    public function iterationDataProvider(): array
    {
        return [
            'Empty store data and store config data' => [
                'storeData' => [],
                'storeConfigData' => [],
                'expectedResult' => []
            ],
            'Empty store data' => [
                'storeData' => [],
                'storeConfigData' => [
                    [
                        "id" => 1,
                        "code"  => "default",
                        "website_id" => 1,
                        "locale" => "en_US",
                        "base_currency_code" => "USD",
                        "default_display_currency_code" => "USD",
                        "timezone" => "America/Los_Angeles",
                        "weight_unit" => "lbs",
                        "base_url" => "http =>//mage2.dv/",
                        "base_link_url" => "http =>//mage2.dv/",
                        "base_static_url" => "http =>//mage2.dv/static/version1588517752/",
                        "base_media_url" => "http =>//mage2.dv/media/",
                        "secure_base_url" => "http =>//mage2.dv/",
                        "secure_base_link_url" => "http =>//mage2.dv/",
                        "secure_base_static_url" => "http =>//mage2.dv/static/version1588517752/",
                        "secure_base_media_url" => "http://mage2.dv/media/"
                    ]
                ],
                'expectedResult' => []
            ],
            'Empty store config data' => [
                'storeData' => [
                    [
                        "id" => 1,
                        "code" => "default",
                        "name" => "Default Store View",
                        "website_id" => 1,
                        "store_group_id" => 1,
                        "is_active" => 1
                    ]
                ],
                'storeConfigData' => [],
                'expectedResult' => [
                    [
                        "id" => 1,
                        "code" => "default",
                        "name" => "Default Store View",
                        "website_id" => 1,
                        "store_group_id" => 1,
                        "is_active" => 1
                    ]
                ]
            ],
            'Skip admin store' => [
                'storeData' => [
                    [
                        "id" => 0,
                        "code" => "admin",
                        "name" => "Admin Store View",
                        "website_id" => 0,
                        "store_group_id" => 0,
                        "is_active" => 1
                    ]
                ],
                'storeConfigData' => [],
                'expectedResult' => []
            ],
            'Process invalid store data, to have clear validation messages' => [
                'storeData' => [
                    [
                        "code" => "default",
                        "name" => "Default Store View",
                        "website_id" => 1,
                        "store_group_id" => 1,
                        "is_active" => 1
                    ]
                ],
                'storeConfigData' => [
                    [
                        "id" => 1,
                        "code"  => "default",
                        "website_id" => 1,
                        "locale" => "en_US",
                        "base_currency_code" => "USD",
                        "default_display_currency_code" => "USD",
                        "timezone" => "America/Los_Angeles",
                        "weight_unit" => "lbs",
                        "base_url" => "http =>//mage2.dv/",
                        "base_link_url" => "http =>//mage2.dv/",
                        "base_static_url" => "http =>//mage2.dv/static/version1588517752/",
                        "base_media_url" => "http =>//mage2.dv/media/",
                        "secure_base_url" => "http =>//mage2.dv/",
                        "secure_base_link_url" => "http =>//mage2.dv/",
                        "secure_base_static_url" => "http =>//mage2.dv/static/version1588517752/",
                        "secure_base_media_url" => "http://mage2.dv/media/"
                    ]
                ],
                'expectedResult' => [
                    [
                        "code" => "default",
                        "name" => "Default Store View",
                        "website_id" => 1,
                        "store_group_id" => 1,
                        "is_active" => 1
                    ]
                ]
            ],
            'Store data' => [
                'storeData' => [
                    [
                        "id" => 0,
                        "code" => "admin",
                        "name" => "Admin Store View",
                        "website_id" => 0,
                        "store_group_id" => 0,
                        "is_active" => 1
                    ],
                    [
                        "id" => 1,
                        "code" => "default",
                        "name" => "Default Store View",
                        "website_id" => 1,
                        "store_group_id" => 1,
                        "is_active" => 1
                    ]
                ],
                'storeConfigData' => [
                    [
                        "id" => 1,
                        "code"  => "default",
                        "website_id" => 1,
                        "locale" => "en_US",
                        "base_currency_code" => "USD",
                        "default_display_currency_code" => "USD",
                        "timezone" => "America/Los_Angeles",
                        "weight_unit" => "lbs",
                        "base_url" => "http =>//mage2.dv/",
                        "base_link_url" => "http =>//mage2.dv/",
                        "base_static_url" => "http =>//mage2.dv/static/version1588517752/",
                        "base_media_url" => "http =>//mage2.dv/media/",
                        "secure_base_url" => "http =>//mage2.dv/",
                        "secure_base_link_url" => "http =>//mage2.dv/",
                        "secure_base_static_url" => "http =>//mage2.dv/static/version1588517752/",
                        "secure_base_media_url" => "http://mage2.dv/media/"
                    ]
                ],
                'expectedResult' => [
                    [
                        "id" => 1,
                        "code"  => "default",
                        "website_id" => 1,
                        "locale" => "en_US",
                        "base_currency_code" => "USD",
                        "default_display_currency_code" => "USD",
                        "timezone" => "America/Los_Angeles",
                        "weight_unit" => "lbs",
                        "base_url" => "http =>//mage2.dv/",
                        "base_link_url" => "http =>//mage2.dv/",
                        "base_static_url" => "http =>//mage2.dv/static/version1588517752/",
                        "base_media_url" => "http =>//mage2.dv/media/",
                        "secure_base_url" => "http =>//mage2.dv/",
                        "secure_base_link_url" => "http =>//mage2.dv/",
                        "secure_base_static_url" => "http =>//mage2.dv/static/version1588517752/",
                        "secure_base_media_url" => "http://mage2.dv/media/",
                        "name" => "Default Store View",
                        "store_group_id" => 1,
                        "is_active" => 1
                    ]
                ]
            ]
        ];
    }
}
