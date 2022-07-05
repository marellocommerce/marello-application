<?php

namespace Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\Entity\SalesChannelType;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadSalesData extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const CHANNEL_1_REF = 'channel1';
    const CHANNEL_2_REF = 'channel2';
    const CHANNEL_3_REF = 'channel3';
    const CHANNEL_4_REF = 'channel4';

    /**
     * @var ObjectManager $manager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        self::CHANNEL_1_REF => [
            'name' => 'Channel-EUR',
            'code' => 'chan_eur',
            'type' => 'magento',
            'currency' => 'EUR',
            'active' => true,
            'default' => true,
        ],
        self::CHANNEL_2_REF => [
            'name' => 'Channel-USD',
            'code' => 'chan_usd',
            'type' => 'pos',
            'currency' => 'USD',
            'active' => true,
            'default' => false,
        ],
        self::CHANNEL_3_REF => [
            'name' => 'Channel-GBP',
            'code' => 'chan_gbp',
            'type' => 'pos',
            'currency' => 'GBP',
            'active' => false,
            'default' => false,
        ],
        self::CHANNEL_4_REF => [
            'name' => 'Channel-UAH',
            'code' => 'chan_uah',
            'type' => 'pos',
            'currency' => 'UAH',
            'active' => false,
            'default' => false,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadSalesChannels();
    }

    /**
     * load and create SalesChannels
     */
    protected function loadSalesChannels()
    {
        $organization = $this->manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->container->get('oro_security.acl_helper');
        $defaultSystemGroup = $this->manager
            ->getRepository(SalesChannelGroup::class)
            ->findSystemChannelGroup($aclHelper);

        foreach ($this->data as $ref => $values) {
            $channel = $this->buildChannel($ref, $values);
            $channel
                ->setOwner($organization)
                ->setGroup($defaultSystemGroup);

            $this->manager->persist($channel);
            $this->setReference($ref, $channel);
        }

        $this->manager->flush();
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

        return $channel->setChannelType($this->getChannelType($data['type']))
            ->setCode($data['code'])
            ->setCurrency($data['currency'])
            ->setActive($data['active'])
            ->setDefault($data['default']);
    }

    /**
     * @param string $name
     * @return SalesChannelType
     */
    private function getChannelType($name)
    {
        $existingChannelType = $this->manager->getRepository(SalesChannelType::class)->find($name);
        if ($existingChannelType) {
            return $existingChannelType;
        } else {
            $channelType = new SalesChannelType($name);
            $channelType->setLabel(ucfirst($name));
            $this->manager->persist($channelType);
            $this->manager->flush();

            return $channelType;
        }
    }
}
