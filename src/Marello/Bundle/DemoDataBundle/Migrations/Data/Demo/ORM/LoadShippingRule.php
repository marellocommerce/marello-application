<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\RuleBundle\Entity\Rule;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;

class LoadShippingRule extends AbstractFixture
{
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
        foreach ($this->data as $shippingRuleReferenceName => $config) {
            $rule = new Rule();
            $rule->setName(self::DEFAULT_RULE_NAME)
                ->setEnabled(true)
                ->setSortOrder($config['sort_order']);

            $shippingRuleConfig = new ShippingMethodsConfigsRule();

            $shippingRuleConfig->setRule($rule)
                ->setOrganization($this->getOrganization($manager))
                ->setCurrency($config['currency']);
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
}
