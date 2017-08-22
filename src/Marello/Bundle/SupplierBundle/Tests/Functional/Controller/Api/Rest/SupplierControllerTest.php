<?php

namespace Marello\Bundle\SupplierBundle\Tests\Functional\Controller\Api\Rest;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\SupplierBundle\Tests\Functional\DataFixtures\LoadSupplierData;

class SupplierControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([
            LoadSupplierData::class,
        ]);
    }

    public function testDelete()
    {
        /** @var Supplier $supplier */
        $supplier = $this->getReference(LoadSupplierData::SUPPLIER_2_REF);
        $supplierId = $supplier->getId();
        $this->client->request(
            'DELETE',
            $this->getUrl('marello_supplier_api_delete_supplier', ['id' => $supplierId])
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, Response::HTTP_NO_CONTENT);
    }

    /**
     * Tests deleting a non-existent Supplier. This should return HTTP Not Found.
     */
    public function testDeleteNonExistent()
    {
        $nonExistingSupplierId = 0;
        /** @var EntityRepository $repo */
        $repo = $this->getContainer()->get('doctrine')->getRepository('MarelloSupplierBundle:Supplier');
        $this->assertEmpty($repo->find($nonExistingSupplierId));

        $this->client->request(
            'DELETE',
            $this->getUrl('marello_supplier_api_delete_supplier', ['id' => $nonExistingSupplierId])
        );

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }
}
