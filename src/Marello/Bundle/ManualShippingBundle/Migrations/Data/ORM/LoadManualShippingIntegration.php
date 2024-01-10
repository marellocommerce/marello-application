<?php

namespace Marello\Bundle\ManualShippingBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\ManualShippingBundle\Entity\ManualShippingSettings;
use Marello\Bundle\ManualShippingBundle\Integration\ManualShippingChannelType;
use Marello\Bundle\ManualShippingBundle\Method\ManualShippingMethodType;
use Marello\Bundle\RuleBundle\Entity\Rule;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodConfig;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodTypeConfig;
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

    const DEFAULT_RULE_NAME = 'Default';
    const DEFAULT_RULE_REFERENCE = 'shipping_rule.default';
    const DEFAULT_CHANNEL_NAME = 'Manual Shipping';

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadOrganizationAndBusinessUnitData::class,
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

        $channel = $this->loadIntegration($manager);
        $shippingRule = $this->createDefaultShippingRule($manager);
        $this->addMethodConfigToDefaultShippingRule($channel, $shippingRule);

        $manager->flush();
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
            ->setName(self::DEFAULT_CHANNEL_NAME)
            ->setEnabled(true)
            ->setOrganization($this->getOrganization($manager))
            ->setTransport($transport);

        $manager->persist($channel);
        $manager->flush();

        return $channel;
    }

    private function createDefaultShippingRule(ObjectManager $manager): ShippingMethodsConfigsRule
    {
        $rule = new Rule();
        $rule->setName(self::DEFAULT_RULE_NAME)
            ->setEnabled(true)
            ->setSortOrder(1);

        $shippingRule = new ShippingMethodsConfigsRule();

        $shippingRule->setRule($rule)
            ->setOrganization($this->getOrganization($manager))
            ->setCurrency('USD');

        $manager->persist($shippingRule);
        $this->addReference(self::DEFAULT_RULE_REFERENCE, $shippingRule);

        return $shippingRule;
    }

    private function addMethodConfigToDefaultShippingRule(
        Channel $channel,
        ShippingMethodsConfigsRule $shippingRule
    ) {
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

        $shippingRule->addMethodConfig($methodConfig);
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
