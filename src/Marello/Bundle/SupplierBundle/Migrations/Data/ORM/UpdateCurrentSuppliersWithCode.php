<?php

namespace Marello\Bundle\SupplierBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Marello\Bundle\SupplierBundle\Entity\Supplier;

class UpdateCurrentSuppliersWithCode extends AbstractFixture
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
     * update current Suppliers with code based on name, but stripped of excluded characters
     */
    public function updateCurrentSuppliers()
    {
        $suppliers = $this->manager
            ->getRepository('MarelloSupplierBundle:Supplier')
            ->findAll();
        /** @var Supplier $supplier */
        foreach ($suppliers as $supplier) {
            $code = preg_replace('/[^a-zA-Z0-9\']/', '_', $supplier->getName());
            $supplier->setCode($code);
            $this->manager->persist($supplier);
        }
        $this->manager->flush();
    }
}
