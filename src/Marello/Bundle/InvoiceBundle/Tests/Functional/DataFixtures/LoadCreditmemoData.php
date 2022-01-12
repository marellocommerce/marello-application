<?php

namespace Marello\Bundle\InvoiceBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\InvoiceBundle\Mapper\RefundToCreditmemoMapper;
use Marello\Bundle\RefundBundle\Tests\Functional\DataFixtures\LoadRefundData;

class LoadCreditmemoData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
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
            LoadRefundData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var RefundToCreditmemoMapper $creditmemoMapper */
        $creditmemoMapper = $this->container->get('marello_invoice.mapper.refund_to_creditmemo');
        for ($createdRefunds = 0; $createdRefunds <= 3; $createdRefunds++) {
            if ($this->hasReference(sprintf('marello_refund_%s', $createdRefunds))) {
                /** @var Refund $refund */
                $refund = $this->getReference(sprintf('marello_refund_%s', $createdRefunds));
                $creditmemo = $creditmemoMapper->map($refund);
                $manager->persist($creditmemo);

                $this->setReference(sprintf('marello_creditmemo_%s', $createdRefunds), $creditmemo);
            }
        }

        $manager->flush();
    }
}
