<?php

namespace Marello\Bundle\ManualShippingBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\ManualShippingBundle\Entity\ManualShippingSettings;
use Marello\Bundle\ManualShippingBundle\Integration\ManualShippingChannelType;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadManualShippingIntegration extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const REFERENCE_MANUAL_SHIPPING = 'manual_shipping_integration';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $label = (new LocalizedFallbackValue())->setString('Manual Shipping');

        $transport = new ManualShippingSettings();
        $transport->addLabel($label);

        $channel = new Channel();
        $channel->setType(ManualShippingChannelType::TYPE)
            ->setName('Manual Shipping')
            ->setEnabled(true)
            ->setTransport($transport)
            ->setOrganization($this->getOrganization());

        $manager->persist($channel);
        $manager->flush();

        $this->setReference(self::REFERENCE_MANUAL_SHIPPING, $channel);
    }


    /**
     * @return Organization
     */
    private function getOrganization()
    {
        return $this->container->get('doctrine')
            ->getRepository('OroOrganizationBundle:Organization')
            ->getFirst();
    }
}
