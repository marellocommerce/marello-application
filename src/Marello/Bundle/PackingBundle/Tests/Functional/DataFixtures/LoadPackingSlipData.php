<?php

namespace Marello\Bundle\PackingBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadAllocationData;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadPackingSlipData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    const COUNT = 5;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadAllocationData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $mapper = $this->container->get('marello_packing.mapper.order_to_packingslip');
        $alloRepo = $manager->getRepository(Allocation::class);
        $allocations  = $alloRepo->findAll();
        $organization = $manager
            ->getRepository(Organization::class)
            ->getFirst();
        /** @var Allocation $allocation */
        foreach ($allocations as $allocation) {
            $allocation->setOrganization($organization);
            $allocation->setWarehouse($this->getReference(LoadOrderData::DEFAULT_WAREHOUSE_REF));
            foreach ($allocation->getItems() as $item) {
                $item->setOrganization($organization);
            }

            $packingSlips = $mapper->map($allocation);

            foreach ($packingSlips as $k => $packingSlip) {
                $manager->persist($packingSlip);
                $this->setReference(sprintf('packing_slip.%d', $k), $packingSlip);
            }
        }
        
        $manager->flush();
    }
}
