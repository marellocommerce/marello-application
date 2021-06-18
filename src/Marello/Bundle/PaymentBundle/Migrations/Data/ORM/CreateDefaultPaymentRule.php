<?php

namespace Marello\Bundle\PaymentBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Marello\Bundle\RuleBundle\Entity\Rule;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;

class CreateDefaultPaymentRule extends AbstractFixture implements DependentFixtureInterface
{
    const DEFAULT_RULE_NAME = 'Default';
    const DEFAULT_RULE_REFERENCE = 'payment_rule.default';

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

        $paymentRule = new PaymentMethodsConfigsRule();

        $paymentRule->setRule($rule)
            ->setOrganization($this->getOrganization($manager))
            ->setCurrency('USD');

        $manager->persist($paymentRule);
        $this->addReference(self::DEFAULT_RULE_REFERENCE, $paymentRule);
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
