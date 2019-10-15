<?php

namespace Marello\Bundle\OroCommerceBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceOrderConnector;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceProductConnector;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData;

class LoadChannelData extends AbstractOroCommerceFixture implements DependentFixtureInterface
{
    protected $channelData = array(
        array(
            'name' => 'orocommerce',
            'type' => 'orocommerce',
            'transport' => 'orocommerce_transport:first_test_transport',
            'connectors' => [OroCommerceOrderConnector::TYPE, OroCommerceProductConnector::TYPE],
            'enabled' => true,
            'reference' => 'orocommerce_channel:first_test_channel',
            'synchronizationSettings' => [
                'isTwoWaySyncEnabled' => true
            ],
        )
    );
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('oro_user.manager');
        $admin = $userManager->findUserByEmail(LoadAdminUserData::DEFAULT_ADMIN_EMAIL);
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        foreach ($this->channelData as $data) {
            $entity = new Channel();

            $data['transport'] = $this->getReference($data['transport']);

            $entity->setDefaultUserOwner($admin);
            $entity->setOrganization($organization);

            $this->setEntityPropertyValues($entity, $data, array('reference', 'synchronizationSettings'));
            $this->setReference($data['reference'], $entity);

            if (isset($data['synchronizationSettings'])) {
                foreach ($data['synchronizationSettings'] as $key => $value) {
                    $entity->getSynchronizationSettingsReference()->offsetSet($key, $value);
                }
            }

            $manager->persist($entity);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return array(
            LoadTransportData::class
        );
    }
}
