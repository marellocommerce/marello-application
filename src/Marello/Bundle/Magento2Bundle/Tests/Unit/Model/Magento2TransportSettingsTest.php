<?php

namespace Marello\Bundle\Magento2Bundle\Tests\Unit\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\Magento2Bundle\Model\Magento2TransportSettings;
use Marello\Bundle\Magento2Bundle\Model\WebsiteToSalesChannelMapItem;
use PHPUnit\Framework\TestCase;

class Magento2TransportSettingsTest extends TestCase
{
    public function testTransportSettings()
    {
        $apiUrl = 'http://mag.dv';
        $apiToken = 'API_TOKEN';
        $syncStarDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));
        $initialSyncStarDate = new \DateTime('1998-01-01 00:00:00', new \DateTimeZone('UTC'));
        $deleteDataOnDeactivation = true;
        $deleteDataOnDeletion = true;
        $lastWebsiteToSalesChannelMapItem = new WebsiteToSalesChannelMapItem(
            5,
            'MAG2 Website',
            7,
            'MAR2 SalesChannel'
        );
        $websitesToSalesChannelMapItems = new ArrayCollection([
            new WebsiteToSalesChannelMapItem(
                1,
                'Main Website',
                1,
                'Main SalesChannel'
            ),
            new WebsiteToSalesChannelMapItem(
                2,
                'MAG Website',
                3,
                'MAR SalesChannel'
            ),
            $lastWebsiteToSalesChannelMapItem,
        ]);

        $settings = new Magento2TransportSettings(
            [
                Magento2TransportSettings::API_URL_KEY => $apiUrl,
                Magento2TransportSettings::API_TOKEN_KEY => $apiToken,
                Magento2TransportSettings::SYNC_START_DATE_KEY => $syncStarDate,
                Magento2TransportSettings::INITIAL_SYNC_START_DATE_KEY => $initialSyncStarDate,
                Magento2TransportSettings::WEBSITE_TO_SALES_CHANNEL_MAP_ITEMS_KEY => $websitesToSalesChannelMapItems,
                Magento2TransportSettings::DELETE_REMOTE_DATA_ON_DEACTIVATION_KEY => $deleteDataOnDeactivation,
                Magento2TransportSettings::DELETE_REMOTE_DATA_ON_DELETION_KEY => $deleteDataOnDeletion
            ]
        );

        $this->assertSame($apiUrl, $settings->getApiUrl());
        $this->assertSame($apiToken, $settings->getApiToken());
        $this->assertEquals($syncStarDate, $settings->getSyncStartDate());
        $this->assertEquals($initialSyncStarDate, $settings->getInitialSyncStartDate());
        $this->assertSame($websitesToSalesChannelMapItems, $settings->getWebsiteToSalesChannelMapItems());
        $this->assertSame($deleteDataOnDeactivation, $settings->isDeleteRemoteDataOnDeactivation());
        $this->assertSame($deleteDataOnDeletion, $settings->isDeleteRemoteDataOnDeletion());

        $this->assertSame(
            $lastWebsiteToSalesChannelMapItem->getSalesChannelId(),
            $settings->getSalesChannelIdByWebsiteOriginId($lastWebsiteToSalesChannelMapItem->getWebsiteOriginId())
        );
    }
}
