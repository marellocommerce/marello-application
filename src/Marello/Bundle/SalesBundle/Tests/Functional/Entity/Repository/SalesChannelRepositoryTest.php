<?php

namespace Marello\Bundle\SalesBundle\Tests\Functional\Entity\Repository;

use Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
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
    protected function setUp(): void
    {
        $this->initClient([], static::generateBasicAuthHeader());
        $this->loadFixtures([
            LoadSalesData::class,
        ]);

        $this->repository = static::getContainer()->get(SalesChannelRepository::class);
    }

    public function testGetActiveChannels()
    {
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        // active and default
        static::assertContains(
            $this->getReference(LoadSalesData::CHANNEL_1_REF),
            $this->repository->getActiveChannels($aclHelper)
        );

        // active and not default
        static::assertContains(
            $this->getReference(LoadSalesData::CHANNEL_2_REF),
            $this->repository->getActiveChannels($aclHelper)
        );

        // not active and not default
        static::assertNotContains(
            $this->getReference(LoadSalesData::CHANNEL_3_REF),
            $this->repository->getActiveChannels($aclHelper)
        );
    }

    public function testGetDefaultActiveChannels()
    {
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        // active and default
        static::assertContains(
            $this->getReference(LoadSalesData::CHANNEL_1_REF),
            $this->repository->getDefaultActiveChannels($aclHelper)
        );

        // active and not default
        static::assertNotContains(
            $this->getReference(LoadSalesData::CHANNEL_2_REF),
            $this->repository->getDefaultActiveChannels($aclHelper)
        );

        // not active and not default
        static::assertNotContains(
            $this->getReference(LoadSalesData::CHANNEL_3_REF),
            $this->repository->getDefaultActiveChannels($aclHelper)
        );
    }
}
