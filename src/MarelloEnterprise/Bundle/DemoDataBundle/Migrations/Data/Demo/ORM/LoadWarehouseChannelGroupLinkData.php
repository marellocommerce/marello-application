<?php

namespace MarelloEnterprise\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadSalesChannelGroupData;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class LoadWarehouseChannelGroupLinkData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var Organization
     */
    protected $organization;

    /**
     * @var array
     */
    protected $data = [
        'whscglink1' => [
            'channelgroup'         => 'Europe Group',
            'warehousegroup'       => 'Europe'
        ],
        'whscglink2' => [
            'channelgroup'         => 'US Group',
            'warehousegroup'       => 'US'
        ],
        'whscglink3' => [
            'channelgroup'         => 'Sales Channel DE Berlin',
            'warehousegroup'       => 'Store Warehouse DE Berlin'
        ],
        'whscglink4' => [
            'channelgroup'         => 'Sales Channel DE Dortmund',
            'warehousegroup'       => 'Store Warehouse DE Dortmund'
        ],
        'whscglink5' => [
            'channelgroup'         => 'Sales Channel DE Frankfurt',
            'warehousegroup'       => 'Store Warehouse DE Frankfurt'
        ],
        'whscglink6' => [
            'channelgroup'         => 'Sales Channel DE München',
            'warehousegroup'       => 'Store Warehouse DE München'
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadSalesChannelGroupData::class,
            LoadWarehouseData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->organization = $this->getOrganization();

        $this->loadWarehouseChannelGroupLinks();
    }

    /**
     * Get organization
     * @return Organization
     */
    protected function getOrganization()
    {
        return $this->manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
    }

    /**
     * load WarehouseChannelGroupLinks
     */
    public function loadWarehouseChannelGroupLinks()
    {
        foreach ($this->data as $warehouseKey => $data) {
            /** @var SalesChannelGroup $channelGroup */
            $channelGroup = $this->getReference($data['channelgroup']);
            /** @var WarehouseGroup $warehouseGroup */
            $warehouseGroup = $this->getReference(sprintf('warehouse.%s', $data['warehousegroup']));

            $channelLink = $this->getExistingWarehouseChannelGroupLink($warehouseGroup);
            if (!$channelLink) {
                $channelLink = new WarehouseChannelGroupLink();
                $channelLink
                    ->setOrganization($this->organization)
                    ->setWarehouseGroup($warehouseGroup)
                    ->setSystem(false);
            }

            $channelLink->addSalesChannelGroup($channelGroup);
            $this->manager->persist($channelLink);
            $this->manager->flush();
        }
    }

    /**
     * Get WarehouseChannelGroupLink
     * @param $warehouseGroup
     * @return WarehouseChannelGroupLink
     */
    private function getExistingWarehouseChannelGroupLink($warehouseGroup)
    {
        return $this->manager->getRepository(WarehouseChannelGroupLink::class)->findOneBy(
            [
                'warehouseGroup' => $warehouseGroup
            ]
        );
    }
}
