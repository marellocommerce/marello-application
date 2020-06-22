<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;

class LoadWarehouseData extends AbstractFixture
{
    const WAREHOUSE_1_REF = 'warehouse1';
    const WAREHOUSE_2_REF = 'warehouse2';
    const WAREHOUSE_3_REF = 'warehouse3';

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        self::WAREHOUSE_1_REF => [
            'label' => 'Additional Warehouse1',
            'code' => 'add_warehouse1',
        ],
        self::WAREHOUSE_2_REF => [
            'label' => 'Additional Warehouse2',
            'code' => 'add_warehouse2',
        ],
        self::WAREHOUSE_3_REF => [
            'label' => 'Additional Warehouse3',
            'code' => 'add_warehouse3',
        ],
    ];
    
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        $warehouseType = $manager
            ->getRepository('MarelloInventoryBundle:WarehouseType')
            ->findOneBy(['name' => WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL]);

        foreach ($this->data as $ref => $values) {
            $warehouse = $this->buildWarehouse($ref, $values);
            $warehouse
                ->setOwner($organization)
                ->setWarehouseType($warehouseType);

            $this->manager->persist($warehouse);
            $this->setReference($ref, $warehouse);
        }

        $this->manager->flush();
    }

    /**
     * @param string $reference
     * @param array  $data
     *
     * @return Warehouse
     */
    private function buildWarehouse($reference, $data)
    {
        $warehouseAddress = new MarelloAddress();
        $this->manager->persist($warehouseAddress);

        $warehouse = new Warehouse($data['label'], false);

        return $warehouse
            ->setCode($reference)
            ->setAddress($warehouseAddress);
    }
}
