<?php

namespace Marello\Bundle\ManualShippingBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\ManualShippingBundle\Entity\ManualShippingSettings;
use Marello\Bundle\ManualShippingBundle\Integration\ManualShippingChannelType;
use Marello\Bundle\ManualShippingBundle\Method\ManualShippingMethodType;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodConfig;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodTypeConfig;
use Marello\Bundle\ShippingBundle\Migrations\Data\ORM\CreateDefaultShippingRule;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadManualShippingIntegration extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            CreateDefaultShippingRule::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        if (!$this->container) {
            return;
        }

        if (!$this->container->hasParameter('oro_integration.entity.class')) {
            return;
        }

        $channel = $this->loadIntegration($manager);

        $this->addMethodConfigToDefaultShippingRule($manager, $channel);
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Channel
     */
    private function loadIntegration(ObjectManager $manager)
    {
        $label = (new LocalizedFallbackValue())->setString('Manual Shipping');

        $transport = new ManualShippingSettings();
        $transport->addLabel($label);

        $channel = new Channel();
        $channel->setType(ManualShippingChannelType::TYPE)
            ->setName('Manual Shipping')
            ->setEnabled(true)
            ->setOrganization($this->getOrganization($manager))
            ->setTransport($transport);

        $manager->persist($channel);
        $manager->flush();

        return $channel;
    }

    /**
     * @param ObjectManager $manager
     * @param Channel       $channel
     */
    private function addMethodConfigToDefaultShippingRule(ObjectManager $manager, Channel $channel)
    {
        $typeConfig = new ShippingMethodTypeConfig();
        $typeConfig->setEnabled(true);
        $typeConfig->setType(ManualShippingMethodType::IDENTIFIER)
            ->setOptions([
                ManualShippingMethodType::PRICE_OPTION => 0.0,
                ManualShippingMethodType::TYPE_OPTION => ManualShippingMethodType::PER_ORDER_TYPE,
            ]);

        $methodConfig = new ShippingMethodConfig();
        $methodConfig->setMethod($this->getIdentifier($channel))
            ->addTypeConfig($typeConfig);

        $defaultShippingRule = $this->getReference(CreateDefaultShippingRule::DEFAULT_RULE_REFERENCE);
        $defaultShippingRule->addMethodConfig($methodConfig);

        $manager->persist($defaultShippingRule);
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Organization|object
     */
    private function getOrganization(ObjectManager $manager)
    {
        if ($this->hasReference(LoadOrganizationAndBusinessUnitData::REFERENCE_DEFAULT_ORGANIZATION)) {
            return $this->getReference(LoadOrganizationAndBusinessUnitData::REFERENCE_DEFAULT_ORGANIZATION);
        } else {
            return $manager
                ->getRepository('OroOrganizationBundle:Organization')
                ->getFirst();
        }
    }

    /**
     * @param Channel $channel
     *
     * @return int|string
     */
    private function getIdentifier(Channel $channel)
    {
        return $this->container
            ->get('marello_manual_shipping.method.identifier_generator.method')
            ->generateIdentifier($channel);
    }
}
