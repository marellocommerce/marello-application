<?php

namespace Marello\Bundle\ShippingBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\RuleBundle\Entity\Rule;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;

class CreateDefaultShippingRule extends AbstractFixture implements DependentFixtureInterface
{
    const DEFAULT_RULE_NAME = 'Default';
    const DEFAULT_RULE_REFERENCE = 'shipping_rule.default';

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
}
