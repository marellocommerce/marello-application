<?php

namespace Marello\Bundle\TaxBundle\Tests\Functional\Controller\Api\Rest;

use Doctrine\ORM\EntityRepository;

use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxCodeData;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class TaxCodeControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([
            LoadTaxCodeData::class
        ]);
    }

    public function testDelete()
    {
        /** @var TaxCode $taxCode */
        $taxCode = $this->getReference(LoadTaxCodeData::TAXCODE_2_REF);
        $taxCodeId = $taxCode->getId();
        $this->client->request(
            'DELETE',
            $this->getUrl('marello_tax_api_delete_taxcode', ['id' => $taxCodeId])
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, Response::HTTP_NO_CONTENT);
    }

    /**
     * Tests deleting a non-existent TaxCode. This should return HTTP Not Found.
     */
    public function testDeleteNonExistent()
    {
        $nonExistingTaxCodeId = 0;
        /** @var EntityRepository $repo */
        $repo = $this->getContainer()->get('doctrine')->getRepository('MarelloTaxBundle:TaxCode');
        $this->assertEmpty($repo->find($nonExistingTaxCodeId));

        $this->client->request(
            'DELETE',
            $this->getUrl('marello_tax_api_delete_taxcode', ['id' => $nonExistingTaxCodeId])
        );

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }
}
