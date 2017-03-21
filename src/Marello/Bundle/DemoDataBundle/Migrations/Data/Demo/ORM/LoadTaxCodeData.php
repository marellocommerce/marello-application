<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\TaxBundle\Entity\TaxCode;

class LoadTaxCodeData extends AbstractFixture
{
    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        ['code' => 'TAX_EXEMPT', 'description' => 'No tax applied'],
        ['code' => 'TAX_HIGH', 'description' => 'High tax'],
        ['code' => 'TAX_LOW', 'description' => 'Low tax'],
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
        $i = 0;

        foreach ($this->data as $values) {
            $taxCode = new TaxCode();

            $taxCode
                ->setCode($values['code'])
                ->setDescription($values['description'])
            ;

            $this->manager->persist($taxCode);
            $this->setReference('marello_taxcode_' . $i, $taxCode);
            $i++;
        }

        $this->manager->flush();
    }
}
