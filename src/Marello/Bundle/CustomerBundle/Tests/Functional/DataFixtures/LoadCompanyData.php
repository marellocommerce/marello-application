<?php

namespace Marello\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\CustomerBundle\Entity\Company;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Marello\Bundle\PaymentTermBundle\Tests\Functional\DataFixtures\LoadPaymentTermsData;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class LoadCompanyData extends AbstractFixture implements DependentFixtureInterface
{
    const COMPANY_1_REF = 'company1';
    const COMPANY_2_REF = 'company2';
    const COMPANY_3_REF = 'company3';

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var Organization
     */
    protected $defaultOrganization;

    /**
     * @var array
     */
    protected $data = [
        self::COMPANY_1_REF => [
            'name' => self::COMPANY_1_REF,
            'customers' => [
                'marello-customer-1',
                'marello-customer-2'
            ],
            'children' => [
                self::COMPANY_2_REF
            ]
        ],
        self::COMPANY_2_REF => [
            'name' => self::COMPANY_2_REF,
            'customers' => [
                'marello-customer-3',
                'marello-customer-4'
            ],
            'children' => [
                self::COMPANY_3_REF
            ],
            'paymentTerm' => LoadPaymentTermsData::PAYMENT_TERM_1_REF,
        ],
        self::COMPANY_3_REF => [
            'name' => self::COMPANY_3_REF,
            'customers' => [
                'marello-customer-5',
                'marello-customer-6'
            ]
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadCustomerData::class,
            LoadPaymentTermsData::class,
        ];
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $organizations = $this->manager
            ->getRepository('OroOrganizationBundle:Organization')
            ->findAll();

        if (is_array($organizations) && count($organizations) > 0) {
            $this->defaultOrganization = array_shift($organizations);
        }

        $this->loadCompanies();
        $this->loadChildren();
    }

    /**
     * load categories
     */
    public function loadCompanies()
    {
        foreach ($this->data as $companyRef => $data) {
            $company = $this->createCompany($data);
            $this->setReference($companyRef, $company);
        }

        $this->manager->flush();
    }

    /**
     * load categories
     */
    public function loadChildren()
    {
        foreach ($this->data as $companyRef => $data) {
            $company = $this->getReference($companyRef);
            if (isset($data['children'])) {
                foreach ($data['children'] as $childRef) {
                    /** @var Company $child */
                    $child = $this->getReference($childRef);
                    $child->setParent($company);
                    $this->manager->persist($child);
                    $this->setReference($childRef, $child);
                }
            }
        }

        $this->manager->flush();
    }
    
    /**
     * @param array $data
     * @return Company
     */
    private function createCompany(array $data)
    {
        $company = new Company();
        $company->setName($data['name']);
        $company->setOrganization($this->defaultOrganization);

        if (isset($data['customers'])) {
            foreach ($data['customers'] as $customerRef) {
                /** @var Customer $customer */
                $customer = $this->getReference($customerRef);
                $company->addCustomer($customer);
            }
        }
        if (isset($data['paymentTerm'])) {
            /** @var PaymentTerm $paymentTerm */
            $paymentTerm = $this->getReference($data['paymentTerm']);
            $company->setPaymentTerm($paymentTerm);
        }

        $this->manager->persist($company);

        return $company;
    }
}
