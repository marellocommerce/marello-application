<?php

namespace Marello\Bundle\RefundBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxCodeData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Entity\RefundItem;

class LoadRefundData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var ContainerInterface
     */
    protected $container;

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
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $orders = [
            $this->getReference('marello_order_0'),
            $this->getReference('marello_order_1')
        ];

        foreach ($orders as $refKey => $order) {
            $refund = new Refund();
            $refund->setOrder($order);
            $refund->setCustomer($order->getCustomer());
            $refund->setOrganization($order->getOrganization());
            $refund->setCurrency($order->getCurrency());

            $refundItems = $order
                ->getItems()
                ->map(
                    function (OrderItem $item) {
                        return (new RefundItem())
                            ->setOrderItem($item)
                            ->setQuantity($item->getQuantity())
                            ->setRefundAmount($item->getRowTotalInclTax())
                            ->setBaseAmount($item->getPrice())
                            ->setName($item->getProductName())
                            ->setTaxCode($this->getReference(LoadTaxCodeData::TAXCODE_2_REF));
                    }
                );

            $refundItems->add(
                (new RefundItem())
                    ->setName('Shipping Costs')
                    ->setBaseAmount(10)
                    ->setRefundAmount(10)
                    ->setTaxCode($this->getReference(LoadTaxCodeData::TAXCODE_2_REF))
            );
            $refundItems->map(
                function (RefundItem $item) use ($refund) {
                    $refund->addItem($item);
                }
            );

            $refundGrandTotal = 0.00;
            $refundSubTotal = 0.00;
            $refundTaxTotal = 0.00;
            $refund->getItems()->map(function (RefundItem $item) use (
                &$refundSubTotal,
                &$refundTaxTotal,
                &$refundGrandTotal,
                $refund
            ) {
                if ($item->getTaxCode()) {
                    $taxTotals = $this->container->get('marello_refund.calculator.refund_balance')
                        ->calculateIndividualTaxItem(
                            [
                                'quantity' => $item->getQuantity(),
                                'taxCode' => $item->getTaxCode()->getId(),
                                'refundAmount' => $item->getRefundAmount(),
                            ],
                            $refund
                        );
                    $refundSubTotal += (double)$taxTotals->getExcludingTax();
                    $refundTaxTotal += (double)$taxTotals->getTaxAmount();
                    $refundGrandTotal += (double)$taxTotals->getIncludingTax();
                }
            });

            $refund->setRefundSubtotal($refundSubTotal);
            $refund->setRefundTaxTotal($refundTaxTotal);
            $refund->setRefundAmount($refundGrandTotal);

            $manager->persist($refund);
            $this->setReference(sprintf('marello_refund_%s', $refKey), $refund);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
