<?php

namespace Marello\Bundle\OroCommerceBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;

class LoadAdditionalSalesData extends AbstractOroCommerceFixture implements DependentFixtureInterface
{
    const TEST_SALESCHANNELGROUP_OROCOMMERCE = 'orocommerce_transport:first_test_group';
    const TEST_SALESCHANNEL_OROCOMMERCE = 'orocommerce_transport:first_test_saleschannel';

    /**
     * @var ObjectManager $manager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        self::TEST_SALESCHANNEL_OROCOMMERCE => [
            'name' => 'OroCommerce TEST',
            'code' => 'orocommerce_test',
            'type' => 'orocommerce',
            'currency' => 'USD',
            'active' => true,
            'default' => true
        ]
    ];

    protected $salesChannelGroupData = [
        self::TEST_SALESCHANNELGROUP_OROCOMMERCE => [
            'description' => 'First Test Channel Group',
            'channels' => [self::TEST_SALESCHANNEL_OROCOMMERCE]
        ]
    ];

    public function getDependencies()
    {
        return [LoadProductData::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadOroCommerceSalesChannel();
        $this->loadOroCommerceSalesChannelGroup();
        $this->updateProducts();

        $this->manager->flush();
    }

    protected function updateProducts()
    {
        $products = [
            $this->getReference(LoadProductData::PRODUCT_1_REF),
            $this->getReference(LoadProductData::PRODUCT_2_REF),
            $this->getReference(LoadProductData::PRODUCT_3_REF)
        ];

        /** @var Product $product */
        foreach ($products as $product) {
            $product->addChannel($this->getReference(self::TEST_SALESCHANNEL_OROCOMMERCE));
            $this->manager->persist($product);
        }
    }

    /**
     * load and create SalesChannels
     */
    protected function loadOroCommerceSalesChannel()
    {
        $organization = $this->manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        $defaultSystemGroup = $this->manager
            ->getRepository(SalesChannelGroup::class)
            ->findSystemChannelGroup();

        foreach ($this->data as $ref => $values) {
            $channel = $this->buildChannel($ref, $values);
            $channel
                ->setOwner($organization)
                ->setGroup($defaultSystemGroup);

            $this->manager->persist($channel);
            $this->setReference($ref, $channel);
        }
    }

    /**
     * @param string $reference
     * @param array  $data
     *
     * @return SalesChannel
     */
    private function buildChannel($reference, $data)
    {
        $channel = new SalesChannel($reference);

        return $channel->setChannelType($data['type'])
            ->setCode($data['code'])
            ->setCurrency($data['currency'])
            ->setActive($data['active'])
            ->setDefault($data['default']);
    }

    /**
     * load and create SalesChannels
     */
    protected function loadOroCommerceSalesChannelGroup()
    {
        $organization = $this->manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        foreach ($this->salesChannelGroupData as $ref => $values) {
            $channelGroup = $this->buildChannelGroup($ref, $values);
            $channelGroup
                ->setOrganization($organization);

            $this->manager->persist($channelGroup);
            $this->setReference($ref, $channelGroup);
        }
    }

    /**
     * @param string $reference
     * @param array  $data
     *
     * @return SalesChannelGroup
     */
    private function buildChannelGroup($reference, $data)
    {
        $channelGroup = new SalesChannelGroup($reference);

        $channelGroup->setName($reference)
            ->setDescription($data['description'])
            ->setSystem(false);
        foreach ($data['channels'] as $channel) {
            $channelGroup->addSalesChannel($this->getReference($channel));
        }

        return $channelGroup;
    }
}
