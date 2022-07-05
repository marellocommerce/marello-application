<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Entity\Repository;

use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class WarehouseRepositoryTest extends WebTestCase
{
    /** @var WarehouseRepository */
    protected $repository;

    public function setUp(): void
    {
        $this->initClient();

        $this->repository = $this->getContainer()->get('doctrine')->getRepository('MarelloInventoryBundle:Warehouse');
    }

    /**
     * Tests if default really returns default warehouse.
     */
    public function testGetDefault()
    {
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        $result = $this->repository->getDefault($aclHelper);

        $this->assertTrue($result->isDefault(), 'Result of getDefault should be default repository.');
    }
}
