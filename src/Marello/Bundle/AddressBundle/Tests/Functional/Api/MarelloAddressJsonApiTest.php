<?php

namespace Marello\Bundle\AddressBundle\Tests\Functional\Api;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Symfony\Component\HttpFoundation\Response;

use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;

use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadCustomerData;

class MarelloAddressJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'marelloaddresses';

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([
            LoadCustomerData::class
        ]);
    }

    public function testCreate()
    {
        $this->post(
            ['entity' => self::TESTING_ENTITY],
            'address_create.yml'
        );

        /** @var MarelloAddress $orderAddress */
        $orderAddress = $this->getEntityManager()
            ->getRepository(MarelloAddress::class)
            ->findOneBy(['city' => 'Rochester']);

        self::assertSame('1215 Caldwell Road', $orderAddress->getStreet());
        self::assertSame('Rochester', $orderAddress->getCity());
        self::assertSame('US', $orderAddress->getCountryIso2());
        self::assertSame('US-NY', $orderAddress->getRegion()->getCombinedCode());
    }
}
