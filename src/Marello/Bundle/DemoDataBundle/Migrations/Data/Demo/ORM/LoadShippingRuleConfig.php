<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\ShippingBundle\Entity\ShippingMethodConfig;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodTypeConfig;
use Marello\Bundle\ManualShippingBundle\Method\ManualShippingMethodType;
use Marello\Bundle\ManualShippingBundle\Integration\ManualShippingChannelType;

class LoadShippingRuleConfig extends AbstractFixture implements
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
            LoadShippingRule::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $channel = $this->loadIntegration($manager);
        if (!$channel) {
            return;
        }

        $this->addMethodConfigToDefaultShippingRule($manager, $channel);
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Channel
     */
    private function loadIntegration(ObjectManager $manager)
    {
        $channels = $manager->getRepository(Channel::class)->findByType(ManualShippingChannelType::TYPE);
        return array_shift($channels);
    }

    /**
     * @param ObjectManager $manager
     * @param Channel       $channel
     */
    private function addMethodConfigToDefaultShippingRule(ObjectManager $manager, Channel $channel)
    {
        foreach ($this->getShippingRuleReferences() as $shippingRuleReference) {
            $typeConfig = new ShippingMethodTypeConfig();
            $typeConfig->setEnabled(true);
            $typeConfig->setType(ManualShippingMethodType::IDENTIFIER)
                ->setOptions([
                    ManualShippingMethodType::PRICE_OPTION => 5.00,
                    ManualShippingMethodType::TYPE_OPTION => ManualShippingMethodType::PER_ORDER_TYPE,
                ]);

            $methodConfig = new ShippingMethodConfig();
            $methodConfig->setMethod($this->getIdentifier($channel))
                ->addTypeConfig($typeConfig);

            $shippingRuleReference->addMethodConfig($methodConfig);
            $manager->persist($shippingRuleReference);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    protected function getShippingRuleReferences()
    {
        return [
            $this->getReference(LoadShippingRule::DEFAULT_RULE_REFERENCE),
            $this->getReference(LoadShippingRule::DEFAULT_RULE_REFERENCE_GBP)
        ];
    }

    /**
     * Get organization
     * @param ObjectManager $manager
     * @return Organization
     */
    protected function getOrganization(ObjectManager $manager)
    {
        return $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
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
