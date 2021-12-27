<?php

namespace Marello\Bundle\CustomerBundle\Tests\Functional\Api;

use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\CustomerBundle\Entity\Company;
use Marello\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCompanyData;
use Symfony\Component\HttpFoundation\Response;

class CompanyJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'marellocompanies';

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadCompanyData::class
        ]);
    }

    /**
     * Test cget (getting a list of companies) of Company entity
     */
    public function testGetListOfCompanies()
    {
        $response = $this->cget(['entity' => self::TESTING_ENTITY], []);

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);


        $this->assertResponseCount(3, $response);
        $this->assertResponseContains('cget_companies_list.yml', $response);
    }

    /**
     * Test get company by id
     */
    public function testGetCompanyById()
    {
        /** @var Company $company */
        $company = $this->getReference(LoadCompanyData::COMPANY_1_REF);
        $response = $this->get(
            ['entity' => self::TESTING_ENTITY, 'id' => $company->getId()],
            []
        );

        $this->assertJsonResponse($response);
        $this->assertResponseContains('get_company_by_id.yml', $response);
    }

    /**
     * Test Create new Company
     */
    public function testCreateNewCompany()
    {
        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'company_create.yml'
        );

        $this->assertJsonResponse($response);
        $responseContent = json_decode($response->getContent());

        /** @var Company $company */
        $company = $this->getEntityManager()->find(Company::class, $responseContent->data->id);
        $this->assertEquals($company->getName(), $responseContent->data->attributes->name);
    }

    /**
     * Test update Company
     */
    public function testUpdateCompany()
    {
        /** @var Company $existingCompany */
        $existingCompany = $this->getReference(LoadCompanyData::COMPANY_1_REF);
        $existingCompanyName = $existingCompany->getName();
        $response = $this->patch(
            [
                'entity' => self::TESTING_ENTITY,
                'id' => $existingCompany->getId()
            ],
            'company_update.yml'
        );

        $this->assertJsonResponse($response);
        $responseContent = json_decode($response->getContent());

        /** @var Company $company */
        $company = $this->getEntityManager()->find(Company::class, $responseContent->data->id);
        $this->assertEquals($company->getName(), $responseContent->data->attributes->name);
        $this->assertNotEquals($company->getName(), $existingCompanyName);
    }
}
