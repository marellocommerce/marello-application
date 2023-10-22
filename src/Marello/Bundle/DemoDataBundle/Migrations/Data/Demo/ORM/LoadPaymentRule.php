<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodConfig;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Marello\Bundle\RuleBundle\Entity\Rule;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class LoadPaymentRule extends AbstractFixture
{
    const DEFAULT_RULE_NAME = 'Payment Term';
    const DEFAULT_RULE_REFERENCE_EUR = 'payment_rule.default_eur';
    const DEFAULT_RULE_REFERENCE_GBP = 'payment_rule.default_gbp';

    protected $data = [
        self::DEFAULT_RULE_REFERENCE_EUR => [
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
        foreach ($this->data as $paymentRuleReferenceName => $config) {
            $rule = new Rule();
            $rule->setName(self::DEFAULT_RULE_NAME)
                ->setEnabled(true)
                ->setSortOrder($config['sort_order']);

            $paymentRuleConfig = new PaymentMethodsConfigsRule();
            $methodConfig = new PaymentMethodConfig();
            $methodConfig->setMethod(self::DEFAULT_RULE_NAME);

            $paymentRuleConfig->setRule($rule)
                ->setOrganization($this->getOrganization($manager))
                ->setCurrency($config['currency'])
                ->addMethodConfig($methodConfig);
            $this->addReference($paymentRuleReferenceName, $paymentRuleConfig);
            $manager->persist($paymentRuleConfig);
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
