<?php

namespace Marello\Bundle\PaymentBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodConfig;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRuleDestination;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRuleDestinationPostalCode;
use Marello\Bundle\PaymentBundle\Tests\Functional\Helper\PaymentTermIntegrationTrait;
use Marello\Bundle\PaymentTermBundle\Tests\Functional\DataFixtures\LoadPaymentTermIntegration;
use Marello\Bundle\RuleBundle\Entity\Rule;
use Marello\Bundle\RuleBundle\Entity\RuleInterface;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Yaml\Yaml;

class LoadPaymentMethodsConfigsRulesWithConfigs extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use PaymentTermIntegrationTrait, ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadPaymentTermIntegration::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getPaymentRuleData() as $reference => $data) {
            $this->loadPaymentRule($reference, $data, $manager);
        }

        $manager->flush();
    }

    /**
     * @return array
     */
    protected function getPaymentRuleData(): array
    {
        return Yaml::parse(file_get_contents(__DIR__.'/data/payment_methods_configs_rules_with_configs.yml'));
    }

    /**
     * @param string        $reference
     * @param array         $data
     * @param ObjectManager $manager
     */
    private function loadPaymentRule($reference, $data, ObjectManager $manager)
    {
        $rule = $this->buildRule($reference, $data);
        $configRule = $this->buildMethodsConfigsRule($reference, $data, $rule, $manager);

        $this->setReference($reference, $configRule);

        $manager->persist($configRule);
    }

    /**
     * @param string        $reference
     * @param array         $data
     * @param RuleInterface $rule
     * @param ObjectManager $manager
     *
     * @return PaymentMethodsConfigsRule
     */
    protected function buildMethodsConfigsRule(
        string $reference,
        array $data,
        RuleInterface $rule,
        ObjectManager $manager
    ) {
        $configRule = new PaymentMethodsConfigsRule();

        $configRule
            ->setRule($rule)
            ->setCurrency($data['currency'])
            ->setOrganization($this->getOrganization());

        $this->setDestinations($configRule, $manager, $data);
        $this->setMethodConfigs($configRule, $manager, $data);

        return $configRule;
    }

    /**
     * @param string $reference
     * @param array $data
     *
     * @return Rule
     */
    protected function buildRule(string $reference, array $data)
    {
        $rule = new Rule();

        $rule->setName($reference)
            ->setEnabled($data['rule']['enabled'])
            ->setSortOrder($data['rule']['sortOrder'])
            ->setExpression($data['rule']['expression']);

        return $rule;
    }

    /**
     * @param PaymentMethodsConfigsRule $configRule
     * @param ObjectManager              $manager
     * @param array                      $data
     */
    private function setDestinations(PaymentMethodsConfigsRule $configRule, ObjectManager $manager, $data)
    {
        if (!array_key_exists('destinations', $data)) {
            return;
        }

        foreach ($data['destinations'] as $destination) {
            /** @var Country $country */
            $country = $manager
                ->getRepository('OroAddressBundle:Country')
                ->findOneBy(['iso2Code' => $destination['country']]);

            $paymentRuleDestination = new PaymentMethodsConfigsRuleDestination();
            $paymentRuleDestination
                ->setMethodsConfigsRule($configRule)
                ->setCountry($country);

            if (array_key_exists('region', $destination)) {
                /** @var Region $region */
                $region = $manager
                    ->getRepository('OroAddressBundle:Region')
                    ->findOneBy(['combinedCode' => $destination['country'].'-'.$destination['region']]);
                $paymentRuleDestination->setRegion($region);
            }

            if (array_key_exists('postalCodes', $destination)) {
                foreach ($destination['postalCodes'] as $postalCode) {
                    $destinationPostalCode = new PaymentMethodsConfigsRuleDestinationPostalCode();
                    $destinationPostalCode->setName($postalCode['name'])
                        ->setDestination($paymentRuleDestination);

                    $paymentRuleDestination->addPostalCode($destinationPostalCode);
                }
            }

            $manager->persist($paymentRuleDestination);
            $configRule->addDestination($paymentRuleDestination);
        }
    }

    /**
     * @param PaymentMethodsConfigsRule $configRule
     * @param ObjectManager              $manager
     * @param array                      $data
     */
    private function setMethodConfigs(PaymentMethodsConfigsRule $configRule, ObjectManager $manager, $data)
    {
        if (!array_key_exists('methodConfigs', $data)) {
            return;
        }

        $methodConfig = $this->buildMethodConfig($configRule);
        $configRule->addMethodConfig($methodConfig);

        $manager->persist($methodConfig);
    }

    /**
     * @param PaymentMethodsConfigsRule $configRule
     *
     * @return PaymentMethodConfig
     */
    private function buildMethodConfig(PaymentMethodsConfigsRule $configRule)
    {
        $methodConfig = new PaymentMethodConfig();

        $methodConfig
            ->setMethodsConfigsRule($configRule)
            ->setMethod($this->getPaymentTermIdentifier());

        return $methodConfig;
    }

    /**
     * @return Organization
     */
    private function getOrganization()
    {
        return $this->container->get('doctrine')
            ->getRepository('OroOrganizationBundle:Organization')
            ->getFirst();
    }
}
