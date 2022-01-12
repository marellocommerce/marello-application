<?php

namespace Marello\Bundle\InvoiceBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\InvoiceBundle\Mapper\OrderToInvoiceMapper;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;

class LoadInvoiceData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /**
     * @var $container ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadOrderData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var OrderToInvoiceMapper $invoiceMapper */
        $invoiceMapper = $this->container->get('marello_invoice.mapper.order_to_invoice');

        for ($createdOrders = 0; $createdOrders <= 3; $createdOrders++) {
            if ($this->hasReference(sprintf('marello_order_%s', $createdOrders))) {
                /** @var Order $order */
                $order = $this->getReference(sprintf('marello_order_%s', $createdOrders));
                $invoice = $invoiceMapper->map($order);
                $manager->persist($invoice);

                $this->setReference(sprintf('marello_invoice_%s', $createdOrders), $invoice);
            }
        }

        $manager->flush();
    }
}
