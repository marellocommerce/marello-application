<?php

namespace Marello\Bundle\SupplierBundle\Tests\Functional\Entity;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\SupplierBundle\Tests\Functional\DataFixtures\LoadSupplierData;

class SupplierOwnerIsSetTest extends WebTestCase
{
    public function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadSupplierData::class
        ]);
    }

    /**
     * Test if the organization is set on the loaded suppliers
     */
    public function testIsSupplierOwnershipIsSet()
    {
        /** @var Supplier $supplier */
        $supplier = $this->getReference(LoadSupplierData::SUPPLIER_3_REF);
        /** @var DoctrineHelper $doctrineHelper */
        $doctrineHelper = self::getContainer()->get('oro_entity.doctrine_helper');
        /** @var Supplier $supplier */
        $supplier = $doctrineHelper
            ->getEntityRepository(Supplier::class)
            ->find($supplier->getId());

        self::assertNotNull($supplier->getOrganization());
    }

    /**
     * Test if the organization is set on a newly created supplier
     */
    public function testCreateNewSupplier()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_supplier_supplier_create'));
        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'Supplier 1';
        $form['marello_supplier_form[name]'] = $name;
        $form['marello_supplier_form[address][country]'] = 'NL';
        $form['marello_supplier_form[address][street]'] = 'Street 1';
        $form['marello_supplier_form[address][city]'] = 'Eindhoven';
        $form['marello_supplier_form[address][postalCode]'] = '5617BC';
        $form['marello_supplier_form[priority]'] = 0;
        $form['marello_supplier_form[canDropship]'] = true;
        $form['marello_supplier_form[isActive]'] = true;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertStringContainsString('Supplier saved', $crawler->html());
        $this->assertStringContainsString($name, $crawler->html());

        $response = $this->client->requestGrid(
            'marello-supplier-grid',
            ['marello-supplier-grid[_filter][name][value]' => $name]
        );

        $this->getJsonResponseContent($response, Response::HTTP_OK);
        /** @var DoctrineHelper $doctrineHelper */
        $doctrineHelper = self::getContainer()->get('oro_entity.doctrine_helper');
        /** @var Supplier $supplier */
        $supplier = $doctrineHelper
            ->getEntityRepository(Supplier::class)
            ->findOneBy(['name' => $name]);

        self::assertNotNull($supplier->getOrganization());
    }
}
