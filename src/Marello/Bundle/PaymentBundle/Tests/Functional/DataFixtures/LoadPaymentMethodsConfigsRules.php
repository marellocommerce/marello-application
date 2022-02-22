<?php

namespace Marello\Bundle\PaymentBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\RuleBundle\Entity\Rule;
use Marello\Bundle\RuleBundle\Entity\RuleInterface;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Symfony\Component\Yaml\Yaml;

class LoadPaymentMethodsConfigsRules extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getPaymentMethodsConfigsRulesData() as $reference => $data) {
            $this->loadPaymentMethodsConfigsRule($reference, $data, $manager);
        }

        $manager->flush();
    }

    /**
     * @return array
     */
    protected function getPaymentMethodsConfigsRulesData()
    {
        return Yaml::parse(file_get_contents(__DIR__.'/data/payment_methods_configs_rules.yml'));
    }

    /**
     * @param string        $reference
     * @param array         $data
     * @param ObjectManager $manager
     */
    private function loadPaymentMethodsConfigsRule($reference, $data, ObjectManager $manager)
    {
        $rule = $this->buildRule($reference, $data['rule']);

        $configRule = $this->createMethodsConfigsRule($rule, $data['currency']);

        $manager->persist($configRule);

        $this->setReference($reference, $configRule);
    }

    /**
     * @param RuleInterface $rule
     * @param               $currency
     *
     * @return PaymentMethodsConfigsRule
     */
    private function createMethodsConfigsRule(RuleInterface $rule, $currency)
    {
        $configRule = new PaymentMethodsConfigsRule();

        return $configRule->setRule($rule)
            ->setCurrency($currency);
    }

    /**
     * @param string $reference
     * @param array  $ruleData
     *
     * @return RuleInterface
     */
    private function buildRule($reference, $ruleData)
    {
        $rule = new Rule();

        return $rule->setName($reference)
            ->setEnabled($ruleData['enabled'])
            ->setSortOrder($ruleData['sortOrder'])
            ->setExpression($ruleData['expression']);
    }
}
