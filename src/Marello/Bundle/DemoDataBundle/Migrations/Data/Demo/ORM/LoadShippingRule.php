<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Marello\Bundle\ManualShippingBundle\Method\ManualShippingMethodType;
use Marello\Bundle\ManualShippingBundle\Migrations\Data\ORM\LoadManualShippingIntegration;
use Marello\Bundle\RuleBundle\Entity\Rule;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodConfig;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodTypeConfig;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadShippingRule extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const DEFAULT_RULE_NAME = 'Manual Shipping';
    const DEFAULT_RULE_REFERENCE = 'shipping_rule.default';
    const DEFAULT_RULE_REFERENCE_GBP = 'shipping_rule.default_gbp';

    protected $data = [
        self::DEFAULT_RULE_REFERENCE => [
            'currency' => 'EUR',
            'sort_order' => 10
        ],
        self::DEFAULT_RULE_REFERENCE_GBP => [
            'currency' => 'GBP',
            'sort_order' => 15
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $channel = $manager->getRepository(Channel::class)->findOneByName(LoadManualShippingIntegration::DEFAULT_CHANNEL_NAME);

        foreach ($this->data as $shippingRuleReferenceName => $config) {
            $rule = new Rule();
            $rule->setName(self::DEFAULT_RULE_NAME)
                ->setEnabled(true)
                ->setSortOrder($config['sort_order']);

            $typeConfig = new ShippingMethodTypeConfig();
            $typeConfig->setEnabled(true);
            $typeConfig->setType(ManualShippingMethodType::IDENTIFIER);
            $typeConfig->setOptions([
                ManualShippingMethodType::PRICE_OPTION => 5.00,
                ManualShippingMethodType::TYPE_OPTION => ManualShippingMethodType::PER_ORDER_TYPE,
            ]);

            $methodConfig = new ShippingMethodConfig();
            $methodConfig->setMethod($this->getIdentifier($channel));
            $methodConfig->addTypeConfig($typeConfig);

            $shippingRuleConfig = new ShippingMethodsConfigsRule();
            $shippingRuleConfig->setRule($rule)
                ->setOrganization($this->getOrganization($manager))
                ->setCurrency($config['currency'])
                ->addMethodConfig($methodConfig);
            $this->addReference($shippingRuleReferenceName, $shippingRuleConfig);
            $manager->persist($shippingRuleConfig);
        }

        $manager->flush();
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

    private function getIdentifier(Channel $channel)
    {
        return $this->container
            ->get('marello_manual_shipping.method.identifier_generator.method')
            ->generateIdentifier($channel);
    }
}
