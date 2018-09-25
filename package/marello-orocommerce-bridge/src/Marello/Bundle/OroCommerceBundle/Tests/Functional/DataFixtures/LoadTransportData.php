<?php

namespace Marello\Bundle\OroCommerceBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;

class LoadTransportData extends AbstractOroCommerceFixture
{
    protected $transportData = array(
        array(
            'reference' => 'orocommerce_transport:first_test_transport',
            'currency' => 'USD',
            'url' => 'https://orocommerce.com/admin',
            'username' => 'admin',
            'key' => '12e25c5f-ec0b-4578-bf95-6a02ffd44f1c',
            'productUnit' => 'each',
            'customerTaxCode' => 1,
            'priceList' => 1,
            'productFamily' => 1,
            'inventoryThreshold' => 1,
            'lowInventoryThreshold' => 1,
            'backOrder' => true
            
        )
    );
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->transportData as $data) {
            $entity = new OroCommerceSettings();
            $this->setEntityPropertyValues($entity, $data, array('reference'));
            $this->setReference($data['reference'], $entity);

            $manager->persist($entity);
        }

        $manager->flush();
    }
}
