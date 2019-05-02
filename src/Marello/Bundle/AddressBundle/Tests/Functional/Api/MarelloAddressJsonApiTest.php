<?php

namespace Marello\Bundle\AddressBundle\Tests\Functional\Api;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\AddressBundle\Tests\Functional\Api\DataFixtures\LoadAddressData;

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
            LoadAddressData::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function testAddressCreate()
    {
        $this->post(
            ['entity' => self::TESTING_ENTITY],
            'address_create.yml'
        );

        /** @var MarelloAddress $marelloAddress */
        $marelloAddress = $this->getEntityManager()
            ->getRepository(MarelloAddress::class)
            ->findOneBy(['city' => 'Rochester']);

        self::assertSame('1215 Caldwell Road', $marelloAddress->getStreet());
        self::assertSame('Rochester', $marelloAddress->getCity());
        self::assertSame('US', $marelloAddress->getCountryIso2());
        self::assertSame('US-NY', $marelloAddress->getRegion()->getCombinedCode());
    }

    /**
     * {@inheritdoc}
     */
    public function testAddressUpdate()
    {
        $existingAddress = $this->getReference(LoadAddressData::ADDRESS_2_REF);
        $response = $this->patch(
            [
                'entity' => self::TESTING_ENTITY,
                'id' => $existingAddress->getId()
            ],
            'address_update.yml'
        );

        $this->assertJsonResponse($response);

        /** @var MarelloAddress $marelloAddress */
        $marelloAddress = $this->getEntityManager()
            ->getRepository(MarelloAddress::class)
            ->find($existingAddress->getId());

        self::assertSame('Torenallee 20', $marelloAddress->getStreet());
        self::assertSame('Eindhoven', $marelloAddress->getCity());
        self::assertSame('123-456-789', $marelloAddress->getPhone());
        self::assertSame('5617BC', $marelloAddress->getPostalCode());
        self::assertSame('NL', $marelloAddress->getCountryIso2());
        self::assertSame('NL-NB', $marelloAddress->getRegion()->getCombinedCode());
    }
}
