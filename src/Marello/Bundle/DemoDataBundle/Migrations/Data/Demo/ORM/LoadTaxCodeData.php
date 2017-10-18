<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\TaxBundle\Entity\TaxCode;

class LoadTaxCodeData extends AbstractFixture
{
    const TAXCODE_0_REF = 'marello_taxcode_0';
    const TAXCODE_1_REF = 'marello_taxcode_1';
    const TAXCODE_2_REF = 'marello_taxcode_2';
    const TAXCODE_3_REF = 'marello_taxcode_3';

    /**
     * @var ObjectManager $manager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        self::TAXCODE_0_REF => [
            'code' => 'DE_high',
            'description' => 'DE High'
        ],
        self::TAXCODE_1_REF => [
            'code' => 'FR_high',
            'description' => 'FR High'
        ],
        self::TAXCODE_2_REF => [
            'code' => 'UK_high',
            'description' => 'UK High'
        ],
        self::TAXCODE_3_REF => [
            'code' => 'US',
            'description' => 'US'
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadTaxCodes();
    }

    /**
     * load and create TaxCodes
     */
    protected function loadTaxCodes()
    {
        foreach ($this->data as $ref => $values) {
            $taxCode = new TaxCode();

            $taxCode
                ->setCode($values['code'])
                ->setDescription($values['description'])
            ;

            $this->manager->persist($taxCode);
            $this->setReference($ref, $taxCode);
        }

        $this->manager->flush();
    }
}
