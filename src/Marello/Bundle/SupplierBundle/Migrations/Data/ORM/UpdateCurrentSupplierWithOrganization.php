<?php

namespace Marello\Bundle\SupplierBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\SupplierBundle\Entity\Supplier;

class UpdateCurrentSupplierWithOrganization extends AbstractFixture
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->updateCurrentSuppliers();
    }

    /**
     * update current Suppliers with organization
     */
    public function updateCurrentSuppliers()
    {
        $organization = $this->manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        $suppliers = $this->manager
            ->getRepository('MarelloSupplierBundle:Supplier')
            ->findBy(['organization' => null]);
        /** @var Supplier $supplier */
        foreach ($suppliers as $supplier) {
            $supplier->setOrganization($organization);
            $this->manager->persist($supplier);
        }
        $this->manager->flush();
    }
}
