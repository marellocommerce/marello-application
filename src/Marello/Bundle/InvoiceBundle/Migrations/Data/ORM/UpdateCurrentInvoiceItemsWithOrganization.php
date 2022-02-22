<?php

namespace Marello\Bundle\InvoiceBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\InvoiceBundle\Entity\CreditmemoItem;
use Marello\Bundle\InvoiceBundle\Entity\InvoiceItem;

class UpdateCurrentInvoiceItemsWithOrganization extends AbstractFixture
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
        $this->updateCurrentInvoiceItems();
        $this->updateCurrentCreditmemoItems();

        $this->manager->flush();
    }

    /**
     * update current InvoiceItems with organization
     */
    public function updateCurrentInvoiceItems()
    {
        $invoiceItems = $this->manager
            ->getRepository(InvoiceItem::class)
            ->findBy(['organization' => null]);

        /** @var InvoiceItem $invoiceItem */
        foreach ($invoiceItems as $invoiceItem) {
            $organization = $invoiceItem->getInvoice()->getOrganization();
            $invoiceItem->setOrganization($organization);
            $this->manager->persist($invoiceItem);
        }
    }

    /**
     * update current CreditMemoItems with organization
     */
    public function updateCurrentCreditmemoItems()
    {
        $creditMemoItems = $this->manager
            ->getRepository(CreditmemoItem::class)
            ->findBy(['organization' => null]);

        /** @var CreditmemoItem $creditMemoItem */
        foreach ($creditMemoItems as $creditMemoItem) {
            $organization = $creditMemoItem->getInvoice()->getOrganization();
            $creditMemoItem->setOrganization($organization);
            $this->manager->persist($creditMemoItem);
        }
    }
}
