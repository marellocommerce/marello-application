<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\RuleBundle\Entity\Rule;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;

class LoadShippingRule extends AbstractFixture
{
    const DEFAULT_EU_RULE_NAME = 'Manual Shipping';
    const DEFAULT_RULE_REFERENCE = 'shipping_rule.default';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $rule = new Rule();
        $rule->setName(self::DEFAULT_EU_RULE_NAME)
            ->setEnabled(true)
            ->setSortOrder(10);

        $shippingRule = new ShippingMethodsConfigsRule();

        $shippingRule->setRule($rule)
            ->setOrganization($this->getOrganization($manager))
            ->setCurrency('EUR');
        $this->addReference(self::DEFAULT_RULE_REFERENCE, $shippingRule);
        $manager->persist($shippingRule);
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
