<?php

namespace Marello\Bundle\InvoiceBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InvoiceBundle\Entity\InvoiceType;

class LoadInvoiceTypesData extends AbstractFixture
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        'invoice' => 'Invoice',
        'creditmemo' => 'Creditmemo',
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadInvoiceTypes();
    }

    /**
     * load and create invoice types
     */
    public function loadInvoiceTypes()
    {
        foreach ($this->data as $name => $label) {
            $type = new InvoiceType($name);
            $type->setLabel($label);
            $this->manager->persist($type);
            $this->setReference('invoice_type_'.$name, $type);
        }

        $this->manager->flush();
    }
}
