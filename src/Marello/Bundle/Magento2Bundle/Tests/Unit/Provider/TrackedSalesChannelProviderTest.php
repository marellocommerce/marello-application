<?php

namespace Marello\Bundle\Magento2Bundle\Tests\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\Magento2Bundle\Entity\Repository\WebsiteRepository;
use Marello\Bundle\Magento2Bundle\Model\SalesChannelInfo;
use Marello\Bundle\Magento2Bundle\Provider\TrackedSalesChannelProvider;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Model\SalesChannelsAwareInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TrackedSalesChannelProviderTest extends TestCase
{
    use EntityTrait;

    /** @var WebsiteRepository|MockObject */
    private $websiteRepository;

    /** @var TrackedSalesChannelProvider */
    private $provider;

    /** @var SalesChannelInfo */
    private $activeSalesChannelWithActiveIntegration;

    /** @var SalesChannelInfo */
    private $disabledSalesChannelWithActiveIntegration;

    /** @var SalesChannelInfo */
    private $activeSalesChannelWithDisabledIntegration;

    /** @var SalesChannelInfo */
    private $disabledSalesChannelWithDisabledIntegration;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->activeSalesChannelWithActiveIntegration = new SalesChannelInfo(
            1,
            1,
            1,
            true,
            true,
            'USD'
        );

        $this->disabledSalesChannelWithActiveIntegration = new SalesChannelInfo(
            2,
            2,
            1,
            false,
            true,
            'EUR'
        );

        $this->activeSalesChannelWithDisabledIntegration = new SalesChannelInfo(
            3,
            3,
            2,
            true,
            false,
            'UAH'
        );

        $this->disabledSalesChannelWithDisabledIntegration = new SalesChannelInfo(
            4,
            4,
            2,
            false,
            false,
            'GBP'
        );

        $this->websiteRepository = $this->createMock(WebsiteRepository::class);
        $this->websiteRepository
            ->method('getSalesChannelInfoArray')
            ->willReturn([
                1 => $this->activeSalesChannelWithActiveIntegration,
                2 => $this->disabledSalesChannelWithActiveIntegration,
                3 => $this->activeSalesChannelWithDisabledIntegration,
                4 => $this->disabledSalesChannelWithDisabledIntegration,
            ]);

        $this->provider = new TrackedSalesChannelProvider($this->websiteRepository);
    }

    /**
     * @dataProvider getSalesChannelsInfoArrayProvider
     *
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @param callable $expectedResultCallback
     */
    public function testGetSalesChannelsInfoArray(
        bool $onlyActiveSalesChannel,
        bool $onlyActiveIntegration,
        callable $expectedResultCallback
    ) {
        $result = $this->provider->getSalesChannelsInfoArray($onlyActiveSalesChannel, $onlyActiveIntegration);
        $this->assertSame($expectedResultCallback($this), $result);
    }

    /**
     * @return array
     */
    public function getSalesChannelsInfoArrayProvider(): array
    {
        return [
            'Case 1. Only active sales channel and active integration' => [
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResultCallback' => function(self $provider) {
                    return [
                        1 => $provider->activeSalesChannelWithActiveIntegration
                    ];
                }
            ],
            'Case 2. Active integration and all sales channel' => [
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResultCallback' => function(self $provider) {
                    return [
                        1 => $provider->activeSalesChannelWithActiveIntegration,
                        2 => $provider->disabledSalesChannelWithActiveIntegration
                    ];
                }
            ],
            'Case 3. Active sales channel and all integration' => [
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResultCallback' => function(self $provider) {
                    return [
                        1 => $provider->activeSalesChannelWithActiveIntegration,
                        3 => $provider->activeSalesChannelWithDisabledIntegration
                    ];
                }
            ],
            'Case 4. All records' => [
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResultCallback' => function(self $provider) {
                    return [
                        1 => $provider->activeSalesChannelWithActiveIntegration,
                        2 => $provider->disabledSalesChannelWithActiveIntegration,
                        3 => $provider->activeSalesChannelWithDisabledIntegration,
                        4 => $provider->disabledSalesChannelWithDisabledIntegration
                    ];
                }
            ],
        ];
    }

    /**
     * @dataProvider isTrackedSalesChannelProvider
     *
     * @param SalesChannel $salesChannel
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @param bool $expectedResult
     */
    public function testIsTrackedSalesChannel(
        SalesChannel $salesChannel,
        bool $onlyActiveSalesChannel,
        bool $onlyActiveIntegration,
        bool $expectedResult
    ) {
        $result = $this->provider->isTrackedSalesChannel(
            $salesChannel,
            $onlyActiveSalesChannel,
            $onlyActiveIntegration
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function isTrackedSalesChannelProvider(): array
    {
        $salesChannelActiveThatAssignedToActiveIntegration = $this->getEntity(
            SalesChannel::class,
            ['id' => 1]
        );

        $salesChannelDisabledThatAssignedToActiveIntegration = $this->getEntity(
            SalesChannel::class,
            ['id' => 2]
        );

        $salesChannelActiveThatAssignedToDisabledIntegration = $this->getEntity(
            SalesChannel::class,
            ['id' => 3]
        );

        $salesChannelDisabledThatAssignedToDisabledIntegration = $this->getEntity(
            SalesChannel::class,
            ['id' => 4]
        );

        $salesChannelNotAttachedToAnyIntegration = $this->getEntity(
            SalesChannel::class,
            ['id' => 5]
        );

        return [
            'Case 1. Only active sales channel and active integration with 1st Sales Channel' => [
                'salesChannel' => $salesChannelActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => true
            ],
            'Case 2. Active integration and all sales channel with 1st Sales Channel' => [
                'salesChannel' => $salesChannelActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => true
            ],
            'Case 3. Active sales channel and all integration with 1st Sales Channel' => [
                'salesChannel' => $salesChannelActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 4. All records with enabled integration with 1st Sales Channel' => [
                'salesChannel' => $salesChannelActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 5. Only active sales channel and active integration with 2nd Sales Channel' => [
                'salesChannel' => $salesChannelDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 6. Active integration and all sales channel with 2nd Sales Channel' => [
                'salesChannel' => $salesChannelDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => true
            ],
            'Case 7. Active sales channel and all integration with 2nd Sales Channel' => [
                'salesChannel' => $salesChannelDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => false
            ],
            'Case 8. All records with enabled integration with 2nd Sales Channel' => [
                'salesChannel' => $salesChannelDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 9. Only active sales channel and active integration with 3rd Sales Channel' => [
                'salesChannel' => $salesChannelActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 10. Active integration and all sales channel with 3rd Sales Channel' => [
                'salesChannel' => $salesChannelActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 11. Active sales channel and all integration with 3rd Sales Channel' => [
                'salesChannel' => $salesChannelActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 12. All records with enabled integration with 3rd Sales Channel' => [
                'salesChannel' => $salesChannelActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 13. Only active sales channel and active integration with 4th Sales Channel' => [
                'salesChannel' => $salesChannelDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 14. Active integration and all sales channel with 4th Sales Channel' => [
                'salesChannel' => $salesChannelDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 15. Active sales channel and all integration with 4th Sales Channel' => [
                'salesChannel' => $salesChannelDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => false
            ],
            'Case 16. All records with enabled integration with 4th Sales Channel' => [
                'salesChannel' => $salesChannelDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 17. Only active sales channel and active integration with 5th Sales Channel' => [
                'salesChannel' => $salesChannelNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 18. Active integration and all sales channel with 5th Sales Channel' => [
                'salesChannel' => $salesChannelNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 19. Active sales channel and all integration with 5th Sales Channel' => [
                'salesChannel' => $salesChannelNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => false
            ],
            'Case 20. All records with enabled integration with 5th Sales Channel' => [
                'salesChannel' => $salesChannelNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => false
            ],
        ];
    }

    /**
     * @dataProvider isTrackedSalesChannelIdProvider
     *
     * @param int $salesChannelId
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @param bool $expectedResult
     */
    public function testIsTrackedSalesChannelId(
        int $salesChannelId,
        bool $onlyActiveSalesChannel,
        bool $onlyActiveIntegration,
        bool $expectedResult
    ) {
        $result = $this->provider->isTrackedSalesChannelId(
            $salesChannelId,
            $onlyActiveSalesChannel,
            $onlyActiveIntegration
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function isTrackedSalesChannelIdProvider(): array
    {
        $salesChannelIdActiveThatAssignedToActiveIntegration = 1;
        $salesChannelIdDisabledThatAssignedToActiveIntegration = 2;
        $salesChannelIdActiveThatAssignedToDisabledIntegration = 3;
        $salesChannelIdDisabledThatAssignedToDisabledIntegration = 4;
        $salesChannelIdNotAttachedToAnyIntegration = 5;

        return [
            'Case 1. Only active sales channel and active integration with 1st Sales Channel' => [
                'salesChannelId' => $salesChannelIdActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => true
            ],
            'Case 2. Active integration and all sales channel with 1st Sales Channel' => [
                'salesChannelId' => $salesChannelIdActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => true
            ],
            'Case 3. Active sales channel and all integration with 1st Sales Channel' => [
                'salesChannelId' => $salesChannelIdActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 4. All records with enabled integration with 1st Sales Channel' => [
                'salesChannelId' => $salesChannelIdActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 5. Only active sales channel and active integration with 2nd Sales Channel' => [
                'salesChannelId' => $salesChannelIdDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 6. Active integration and all sales channel with 2nd Sales Channel' => [
                'salesChannelId' => $salesChannelIdDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => true
            ],
            'Case 7. Active sales channel and all integration with 2nd Sales Channel' => [
                'salesChannelId' => $salesChannelIdDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => false
            ],
            'Case 8. All records with enabled integration with 2nd Sales Channel' => [
                'salesChannelId' => $salesChannelIdDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 9. Only active sales channel and active integration with 3rd Sales Channel' => [
                'salesChannelId' => $salesChannelIdActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 10. Active integration and all sales channel with 3rd Sales Channel' => [
                'salesChannelId' => $salesChannelIdActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 11. Active sales channel and all integration with 3rd Sales Channel' => [
                'salesChannelId' => $salesChannelIdActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 12. All records with enabled integration with 3rd Sales Channel' => [
                'salesChannelId' => $salesChannelIdActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 13. Only active sales channel and active integration with 4th Sales Channel' => [
                'salesChannelId' => $salesChannelIdDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 14. Active integration and all sales channel with 4th Sales Channel' => [
                'salesChannelId' => $salesChannelIdDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 15. Active sales channel and all integration with 4th Sales Channel' => [
                'salesChannelId' => $salesChannelIdDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => false
            ],
            'Case 16. All records with enabled integration with 4th Sales Channel' => [
                'salesChannelId' => $salesChannelIdDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 17. Only active sales channel and active integration with 5th Sales Channel' => [
                'salesChannelId' => $salesChannelIdNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 18. Active integration and all sales channel with 5th Sales Channel' => [
                'salesChannelId' => $salesChannelIdNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 19. Active sales channel and all integration with 5th Sales Channel' => [
                'salesChannelId' => $salesChannelIdNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => false
            ],
            'Case 20. All records with enabled integration with 5th Sales Channel' => [
                'salesChannelId' => $salesChannelIdNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => false
            ],
        ];
    }

    /**
     * @dataProvider isGetIntegrationIdBySalesChannelId
     *
     * @param int $salesChannelId
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @param int $integrationId
     */
    public function testGetIntegrationIdBySalesChannelId(
        int $salesChannelId,
        bool $onlyActiveSalesChannel,
        bool $onlyActiveIntegration,
        int $integrationId = null
    ) {
        $result = $this->provider->getIntegrationIdBySalesChannelId(
            $salesChannelId,
            $onlyActiveSalesChannel,
            $onlyActiveIntegration
        );
        $this->assertSame($integrationId, $result);
    }

    /**
     * @return array
     */
    public function isGetIntegrationIdBySalesChannelId(): array
    {
        $salesChannelIdActiveThatAssignedToActiveIntegration = 1;
        $salesChannelIdDisabledThatAssignedToActiveIntegration = 2;
        $salesChannelIdActiveThatAssignedToDisabledIntegration = 3;
        $salesChannelIdDisabledThatAssignedToDisabledIntegration = 4;
        $salesChannelIdNotAttachedToAnyIntegration = 5;

        return [
            'Case 1. Only active sales channel and active integration with 1st Sales Channel' => [
                'salesChannelId' => $salesChannelIdActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'integrationId' => 1
            ],
            'Case 2. Active integration and all sales channel with 1st Sales Channel' => [
                'salesChannelId' => $salesChannelIdActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'integrationId' => 1
            ],
            'Case 3. Active sales channel and all integration with 1st Sales Channel' => [
                'salesChannelId' => $salesChannelIdActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'integrationId' => 1
            ],
            'Case 4. All records with enabled integration with 1st Sales Channel' => [
                'salesChannelId' => $salesChannelIdActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'integrationId' => 1
            ],
            'Case 5. Only active sales channel and active integration with 2nd Sales Channel' => [
                'salesChannelId' => $salesChannelIdDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'integrationId' => null
            ],
            'Case 6. Active integration and all sales channel with 2nd Sales Channel' => [
                'salesChannelId' => $salesChannelIdDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'integrationId' => 1
            ],
            'Case 7. Active sales channel and all integration with 2nd Sales Channel' => [
                'salesChannelId' => $salesChannelIdDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'integrationId' => null
            ],
            'Case 8. All records with enabled integration with 2nd Sales Channel' => [
                'salesChannelId' => $salesChannelIdDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'integrationId' => 1
            ],
            'Case 9. Only active sales channel and active integration with 3rd Sales Channel' => [
                'salesChannelId' => $salesChannelIdActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'integrationId' => null
            ],
            'Case 10. Active integration and all sales channel with 3rd Sales Channel' => [
                'salesChannelId' => $salesChannelIdActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'integrationId' => null
            ],
            'Case 11. Active sales channel and all integration with 3rd Sales Channel' => [
                'salesChannelId' => $salesChannelIdActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'integrationId' => 2
            ],
            'Case 12. All records with enabled integration with 3rd Sales Channel' => [
                'salesChannelId' => $salesChannelIdActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'integrationId' => 2
            ],
            'Case 13. Only active sales channel and active integration with 4th Sales Channel' => [
                'salesChannelId' => $salesChannelIdDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'integrationId' => null
            ],
            'Case 14. Active integration and all sales channel with 4th Sales Channel' => [
                'salesChannelId' => $salesChannelIdDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'integrationId' => null
            ],
            'Case 15. Active sales channel and all integration with 4th Sales Channel' => [
                'salesChannelId' => $salesChannelIdDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'integrationId' => null
            ],
            'Case 16. All records with enabled integration with 4th Sales Channel' => [
                'salesChannelId' => $salesChannelIdDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'integrationId' => 2
            ],
            'Case 17. Only active sales channel and active integration with 5th Sales Channel' => [
                'salesChannelId' => $salesChannelIdNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'integrationId' => null
            ],
            'Case 18. Active integration and all sales channel with 5th Sales Channel' => [
                'salesChannelId' => $salesChannelIdNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'integrationId' => null
            ],
            'Case 19. Active sales channel and all integration with 5th Sales Channel' => [
                'salesChannelId' => $salesChannelIdNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'integrationId' => null
            ],
            'Case 20. All records with enabled integration with 5th Sales Channel' => [
                'salesChannelId' => $salesChannelIdNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'integrationId' => null
            ],
        ];
    }

    /**
     * @dataProvider hasTrackedSalesChannelsProvider
     *
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @param bool $expectedResult
     */
    public function testHasTrackedSalesChannels(
        bool $onlyActiveSalesChannel,
        bool $onlyActiveIntegration,
        bool $expectedResult
    ) {
        $result = $this->provider->hasTrackedSalesChannels($onlyActiveSalesChannel, $onlyActiveIntegration);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function hasTrackedSalesChannelsProvider(): array
    {
        return [
            'Case 1. Only active sales channel and active integration' => [
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => true
            ],
            'Case 2. Active integration and all sales channel' => [
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => true
            ],
            'Case 3. Active sales channel and all integration' => [
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 4. All records' => [
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
        ];
    }

    /**
     * @dataProvider getSalesChannelInfosByIntegrationIdProvider
     *
     * @param int $integrationId
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @param callable $expectedResultCallback
     */
    public function testGetSalesChannelInfosByIntegrationId(
        int $integrationId,
        bool $onlyActiveSalesChannel,
        bool $onlyActiveIntegration,
        callable $expectedResultCallback
    ) {
        $result = $this->provider->getSalesChannelInfosByIntegrationId(
            $integrationId,
            $onlyActiveSalesChannel,
            $onlyActiveIntegration
        );
        $this->assertSame($expectedResultCallback($this), $result);
    }

    /**
     * @return array
     */
    public function getSalesChannelInfosByIntegrationIdProvider(): array
    {
        $enabledIntegrationId = 1;
        $disabledIntegrationId = 2;
        $notExistedIntegrationId = 3;

        return [
            'Case 1. Only active sales channel and active integration with enabled integration' => [
                'integrationId' => $enabledIntegrationId,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResultCallback' => function(self $provider) {
                    return [
                        1 => $provider->activeSalesChannelWithActiveIntegration
                    ];
                }
            ],
            'Case 2. Active integration and all sales channel with enabled integration' => [
                'integrationId' => $enabledIntegrationId,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResultCallback' => function(self $provider) {
                    return [
                        1 => $provider->activeSalesChannelWithActiveIntegration,
                        2 => $provider->disabledSalesChannelWithActiveIntegration
                    ];
                }
            ],
            'Case 3. Active sales channel and all integration with enabled integration' => [
                'integrationId' => $enabledIntegrationId,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResultCallback' => function(self $provider) {
                    return [
                        1 => $provider->activeSalesChannelWithActiveIntegration
                    ];
                }
            ],
            'Case 4. All records with enabled integration with enabled integration' => [
                'integrationId' => $enabledIntegrationId,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResultCallback' => function(self $provider) {
                    return [
                        1 => $provider->activeSalesChannelWithActiveIntegration,
                        2 => $provider->disabledSalesChannelWithActiveIntegration
                    ];
                }
            ],
            'Case 5. Only active sales channel and active integration with disabled integration' => [
                'integrationId' => $disabledIntegrationId,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResultCallback' => function(self $provider) {
                    return [];
                }
            ],
            'Case 6. Active integration and all sales channel with disabled integration' => [
                'integrationId' => $disabledIntegrationId,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResultCallback' => function(self $provider) {
                    return [];
                }
            ],
            'Case 7. Active sales channel and all integration with disabled integration' => [
                'integrationId' => $disabledIntegrationId,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResultCallback' => function(self $provider) {
                    return [
                        3 => $provider->activeSalesChannelWithDisabledIntegration
                    ];
                }
            ],
            'Case 8. All records with enabled integration with disabled integration' => [
                'integrationId' => $disabledIntegrationId,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResultCallback' => function(self $provider) {
                    return [
                        3 => $provider->activeSalesChannelWithDisabledIntegration,
                        4 => $provider->disabledSalesChannelWithDisabledIntegration
                    ];
                }
            ],
            'Case 9. Only active sales channel and active integration with non-existed integration' => [
                'integrationId' => $notExistedIntegrationId,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResultCallback' => function(self $provider) {
                    return [];
                }
            ],
            'Case 10. Active integration and all sales channel with non-existed integration' => [
                'integrationId' => $notExistedIntegrationId,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResultCallback' => function(self $provider) {
                    return [];
                }
            ],
            'Case 11. Active sales channel and all integration with non-existed integration' => [
                'integrationId' => $notExistedIntegrationId,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResultCallback' => function(self $provider) {
                    return [];
                }
            ],
            'Case 12. All records with enabled integration with non-existed integration' => [
                'integrationId' => $notExistedIntegrationId,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResultCallback' => function(self $provider) {
                    return [];
                }
            ],
        ];
    }

    /**
     * @dataProvider getSalesChannelIdsByIntegrationIdProvider
     *
     * @param int $integrationId
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @param array $expectedResult
     */
    public function testGetSalesChannelIdsByIntegrationId(
        int $integrationId,
        bool $onlyActiveSalesChannel,
        bool $onlyActiveIntegration,
        array $expectedResult
    ) {
        $result = $this->provider->getSalesChannelIdsByIntegrationId(
            $integrationId,
            $onlyActiveSalesChannel,
            $onlyActiveIntegration
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getSalesChannelIdsByIntegrationIdProvider(): array
    {
        $enabledIntegrationId = 1;
        $disabledIntegrationId = 2;
        $notExistedIntegrationId = 3;

        return [
            'Case 1. Only active sales channel and active integration with enabled integration' => [
                'integrationId' => $enabledIntegrationId,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => [1]
            ],
            'Case 2. Active integration and all sales channel with enabled integration' => [
                'integrationId' => $enabledIntegrationId,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => [1,2]
            ],
            'Case 3. Active sales channel and all integration with enabled integration' => [
                'integrationId' => $enabledIntegrationId,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => [1]
            ],
            'Case 4. All records with enabled integration with enabled integration' => [
                'integrationId' => $enabledIntegrationId,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => [1,2]
            ],
            'Case 5. Only active sales channel and active integration with disabled integration' => [
                'integrationId' => $disabledIntegrationId,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => []
            ],
            'Case 6. Active integration and all sales channel with disabled integration' => [
                'integrationId' => $disabledIntegrationId,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => []
            ],
            'Case 7. Active sales channel and all integration with disabled integration' => [
                'integrationId' => $disabledIntegrationId,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => [3]
            ],
            'Case 8. All records with enabled integration with disabled integration' => [
                'integrationId' => $disabledIntegrationId,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => [3,4]
            ],
            'Case 9. Only active sales channel and active integration with non-existed integration' => [
                'integrationId' => $notExistedIntegrationId,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => []
            ],
            'Case 10. Active integration and all sales channel with non-existed integration' => [
                'integrationId' => $notExistedIntegrationId,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => []
            ],
            'Case 11. Active sales channel and all integration with non-existed integration' => [
                'integrationId' => $notExistedIntegrationId,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => []
            ],
            'Case 12. All records with enabled integration with non-existed integration' => [
                'integrationId' => $notExistedIntegrationId,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => []
            ],
        ];
    }

    /**
     * @dataProvider isSalesChannelAwareEntityHasTrackedSalesChannelsProvider
     *
     * @param SalesChannelsAwareInterface $salesAwareEntity
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @param bool $expectedResult
     */
    public function testIsSalesChannelAwareEntityHasTrackedSalesChannels(
        SalesChannelsAwareInterface $salesAwareEntity,
        bool $onlyActiveSalesChannel,
        bool $onlyActiveIntegration,
        bool $expectedResult
    ) {
        $result = $this->provider->isSalesChannelAwareEntityHasTrackedSalesChannels(
            $salesAwareEntity,
            $onlyActiveSalesChannel,
            $onlyActiveIntegration
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function isSalesChannelAwareEntityHasTrackedSalesChannelsProvider(): array
    {
        $salesChannelAwareEntityActiveThatAssignedToActiveIntegration = $this->getEntity(
            Product::class,
            ['channels' => new ArrayCollection([$this->getEntity(SalesChannel::class, ['id' => 1])])]
        );

        $salesChannelAwareEntityDisabledThatAssignedToActiveIntegration = $this->getEntity(
            Product::class,
            ['channels' => new ArrayCollection([$this->getEntity(SalesChannel::class, ['id' => 2])])]
        );

        $salesChannelAwareEntityActiveThatAssignedToDisabledIntegration = $this->getEntity(
            Product::class,
            ['channels' => new ArrayCollection([$this->getEntity(SalesChannel::class, ['id' => 3])])]
        );

        $salesChannelAwareEntityDisabledThatAssignedToDisabledIntegration = $this->getEntity(
            Product::class,
            ['channels' => new ArrayCollection([$this->getEntity(SalesChannel::class, ['id' => 4])])]
        );

        $salesChannelAwareEntityNotAttachedToAnyIntegration = $this->getEntity(
            Product::class,
            ['channels' => new ArrayCollection([$this->getEntity(SalesChannel::class, ['id' => 5])])]
        );

        $salesChannelAwareEntityWithoutSalesChannels = $this->getEntity(Product::class);

        return [
            'Case 1. Only active sales channel and active integration with 1st Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => true
            ],
            'Case 2. Active integration and all sales channel with 1st Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => true
            ],
            'Case 3. Active sales channel and all integration with 1st Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 4. All records with enabled integration with 1st Sales Channel' => [
                'salesAwareEntity' => $salesChannelAwareEntityActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 5. Only active sales channel and active integration with 2nd Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 6. Active integration and all sales channel with 2nd Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => true
            ],
            'Case 7. Active sales channel and all integration with 2nd Product' => [
                'salesChannel' => $salesChannelAwareEntityDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => false
            ],
            'Case 8. All records with enabled integration with 2nd Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 9. Only active sales channel and active integration with 3rd Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 10. Active integration and all sales channel with 3rd Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 11. Active sales channel and all integration with 3rd Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 12. All records with enabled integration with 3rd Product' => [
                'salesChannel' => $salesChannelAwareEntityActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 13. Only active sales channel and active integration with 4th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 14. Active integration and all sales channel with 4th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 15. Active sales channel and all integration with 4th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => false
            ],
            'Case 16. All records with enabled integration with 4th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => true
            ],
            'Case 17. Only active sales channel and active integration with 5th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 18. Active integration and all sales channel with 5th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 19. Active sales channel and all integration with 5th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => false
            ],
            'Case 20. All records with enabled integration with 5th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => false
            ],
            'Case 21. Only active sales channel and active integration with 6th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityWithoutSalesChannels,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 22. Active integration and all sales channel with 6th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityWithoutSalesChannels,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => false
            ],
            'Case 23. Active sales channel and all integration with 6th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityWithoutSalesChannels,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => false
            ],
            'Case 24. All records with enabled integration with 6th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityWithoutSalesChannels,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => false
            ],
        ];
    }

    /**
     * @dataProvider getIntegrationIdsFromSalesChannelAwareEntityProvider
     *
     * @param SalesChannelsAwareInterface $salesAwareEntity
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @param array $expectedResult
     */
    public function testGetIntegrationIdsFromSalesChannelAwareEntity(
        SalesChannelsAwareInterface $salesAwareEntity,
        bool $onlyActiveSalesChannel,
        bool $onlyActiveIntegration,
        array $expectedResult
    ) {
        $result = $this->provider->getIntegrationIdsFromSalesChannelAwareEntity(
            $salesAwareEntity,
            $onlyActiveSalesChannel,
            $onlyActiveIntegration
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getIntegrationIdsFromSalesChannelAwareEntityProvider(): array
    {
        $salesChannelAwareEntityActiveThatAssignedToActiveIntegration = $this->getEntity(
            Product::class,
            ['channels' => new ArrayCollection([$this->getEntity(SalesChannel::class, ['id' => 1])])]
        );

        $salesChannelAwareEntityDisabledThatAssignedToActiveIntegration = $this->getEntity(
            Product::class,
            ['channels' => new ArrayCollection([$this->getEntity(SalesChannel::class, ['id' => 2])])]
        );

        $salesChannelAwareEntityActiveThatAssignedToDisabledIntegration = $this->getEntity(
            Product::class,
            ['channels' => new ArrayCollection([$this->getEntity(SalesChannel::class, ['id' => 3])])]
        );

        $salesChannelAwareEntityDisabledThatAssignedToDisabledIntegration = $this->getEntity(
            Product::class,
            ['channels' => new ArrayCollection([$this->getEntity(SalesChannel::class, ['id' => 4])])]
        );

        $salesChannelAwareEntityNotAttachedToAnyIntegration = $this->getEntity(
            Product::class,
            ['channels' => new ArrayCollection([$this->getEntity(SalesChannel::class, ['id' => 5])])]
        );

        $salesChannelAwareEntityWithoutSalesChannels = $this->getEntity(Product::class);

        return [
            'Case 1. Only active sales channel and active integration with 1st Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => [1 => 1]
            ],
            'Case 2. Active integration and all sales channel with 1st Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => [1 => 1]
            ],
            'Case 3. Active sales channel and all integration with 1st Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => [1 => 1]
            ],
            'Case 4. All records with enabled integration with 1st Sales Channel' => [
                'salesAwareEntity' => $salesChannelAwareEntityActiveThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => [1 => 1]
            ],
            'Case 5. Only active sales channel and active integration with 2nd Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => []
            ],
            'Case 6. Active integration and all sales channel with 2nd Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => [2 => 1]
            ],
            'Case 7. Active sales channel and all integration with 2nd Product' => [
                'salesChannel' => $salesChannelAwareEntityDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => []
            ],
            'Case 8. All records with enabled integration with 2nd Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityDisabledThatAssignedToActiveIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => [2 => 1]
            ],
            'Case 9. Only active sales channel and active integration with 3rd Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => []
            ],
            'Case 10. Active integration and all sales channel with 3rd Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => []
            ],
            'Case 11. Active sales channel and all integration with 3rd Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => [3 => 2]
            ],
            'Case 12. All records with enabled integration with 3rd Product' => [
                'salesChannel' => $salesChannelAwareEntityActiveThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => [3 => 2]
            ],
            'Case 13. Only active sales channel and active integration with 4th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => []
            ],
            'Case 14. Active integration and all sales channel with 4th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => []
            ],
            'Case 15. Active sales channel and all integration with 4th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => []
            ],
            'Case 16. All records with enabled integration with 4th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityDisabledThatAssignedToDisabledIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => [4 => 2]
            ],
            'Case 17. Only active sales channel and active integration with 5th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => []
            ],
            'Case 18. Active integration and all sales channel with 5th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => []
            ],
            'Case 19. Active sales channel and all integration with 5th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => []
            ],
            'Case 20. All records with enabled integration with 5th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityNotAttachedToAnyIntegration,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => []
            ],
            'Case 21. Only active sales channel and active integration with 6th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityWithoutSalesChannels,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResult' => []
            ],
            'Case 22. Active integration and all sales channel with 6th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityWithoutSalesChannels,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResult' => []
            ],
            'Case 23. Active sales channel and all integration with 6th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityWithoutSalesChannels,
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResult' => []
            ],
            'Case 24. All records with enabled integration with 6th Product' => [
                'salesAwareEntity' => $salesChannelAwareEntityWithoutSalesChannels,
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResult' => []
            ],
        ];
    }

    /**
     * @dataProvider getTrackedSalesChannelCurrenciesWithSalesChannelInfosProvider
     *
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @param callable $expectedResultCallback
     */
    public function testGetTrackedSalesChannelCurrenciesWithSalesChannelInfos(
        bool $onlyActiveSalesChannel,
        bool $onlyActiveIntegration,
        callable $expectedResultCallback
    ) {
        $result = $this->provider->getTrackedSalesChannelCurrenciesWithSalesChannelInfos(
            $onlyActiveSalesChannel,
            $onlyActiveIntegration
        );
        $this->assertSame($expectedResultCallback($this), $result);
    }

    /**
     * @return array
     */
    public function getTrackedSalesChannelCurrenciesWithSalesChannelInfosProvider(): array
    {
        return [
            'Case 1. Only active sales channel and active integration' => [
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => true,
                'expectedResultCallback' => function(self $provider) {
                    return [
                        'USD' => [
                            1 => $provider->activeSalesChannelWithActiveIntegration
                        ]
                    ];
                }
            ],
            'Case 2. Active integration and all sales channel' => [
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => true,
                'expectedResultCallback' => function(self $provider) {
                    return [
                        'USD' => [
                            1 => $provider->activeSalesChannelWithActiveIntegration
                        ],
                        'EUR' => [
                            2 => $provider->disabledSalesChannelWithActiveIntegration
                        ],
                    ];
                }
            ],
            'Case 3. Active sales channel and all integration' => [
                'onlyActiveSalesChannel' => true,
                'onlyActiveIntegration' => false,
                'expectedResultCallback' => function(self $provider) {
                    return [
                        'USD' => [
                            1 => $provider->activeSalesChannelWithActiveIntegration
                        ],
                        'UAH' => [
                            3 => $provider->activeSalesChannelWithDisabledIntegration
                        ],
                    ];
                }
            ],
            'Case 4. All records' => [
                'onlyActiveSalesChannel' => false,
                'onlyActiveIntegration' => false,
                'expectedResultCallback' => function(self $provider) {
                    return [
                        'USD' => [
                            1 => $provider->activeSalesChannelWithActiveIntegration
                        ],
                        'EUR' => [
                            2 => $provider->disabledSalesChannelWithActiveIntegration
                        ],
                        'UAH' => [
                            3 => $provider->activeSalesChannelWithDisabledIntegration
                        ],
                        'GBP' => [
                            4 => $provider->disabledSalesChannelWithDisabledIntegration
                        ]
                    ];
                }
            ],
        ];
    }

    public function testClearCache()
    {
        $this->websiteRepository = $this->createMock(WebsiteRepository::class);
        $this->websiteRepository
            ->expects($this->exactly(2))
            ->method('getSalesChannelInfoArray')
            ->willReturn([]);

        $this->provider = new TrackedSalesChannelProvider($this->websiteRepository);
        $this->provider->hasTrackedSalesChannels();

        $this->provider->clearCache();
        $this->provider->hasTrackedSalesChannels();
        /** The last call to check that cache is working */
        $this->provider->hasTrackedSalesChannels();
    }
}
