<?php

namespace Marello\Bundle\SalesBundle\Tests\Functional\Entity\Repository;

use Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class SalesChannelRepositoryTest extends WebTestCase
{
    /**
     * @var SalesChannelRepository
     */
    protected $repository;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient([], static::generateBasicAuthHeader());
        $this->loadFixtures([
            LoadSalesData::class,
        ]);

        $this->repository = static::getContainer()->get('marello_sales.repository.sales_channel');
    }

    public function testGetActiveChannels()
    {
        // active and default
        static::assertContains(
            $this->getReference(LoadSalesData::CHANNEL_1_REF),
            $this->repository->getActiveChannels()
        );

        // active and not default
        static::assertContains(
            $this->getReference(LoadSalesData::CHANNEL_2_REF),
            $this->repository->getActiveChannels()
        );

        // not active and not default
        static::assertNotContains(
            $this->getReference(LoadSalesData::CHANNEL_3_REF),
            $this->repository->getActiveChannels()
        );
    }

    public function testGetDefaultActiveChannels()
    {
        // active and default
        static::assertContains(
            $this->getReference(LoadSalesData::CHANNEL_1_REF),
            $this->repository->getDefaultActiveChannels()
        );

        // active and not default
        static::assertNotContains(
            $this->getReference(LoadSalesData::CHANNEL_2_REF),
            $this->repository->getDefaultActiveChannels()
        );

        // not active and not default
        static::assertNotContains(
            $this->getReference(LoadSalesData::CHANNEL_3_REF),
            $this->repository->getDefaultActiveChannels()
        );
    }
}
