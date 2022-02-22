<?php

namespace Marello\Bundle\ShippingBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Marello\Bundle\ManualShippingBundle\Tests\Functional\DataFixtures\LoadManualShippingIntegration;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Marello\Bundle\RuleBundle\Entity\Rule;
use Marello\Bundle\RuleBundle\Entity\RuleInterface;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodConfig;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRuleDestination;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRuleDestinationPostalCode;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodTypeConfig;
use Marello\Bundle\ShippingBundle\Tests\Functional\Helper\ManualShippingIntegrationTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Yaml\Yaml;

class LoadShippingMethodsConfigsRulesWithConfigs extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use ManualShippingIntegrationTrait, ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadManualShippingIntegration::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getShippingRuleData() as $reference => $data) {
            $this->loadShippingRule($reference, $data, $manager);
        }

        $manager->flush();
    }

    /**
     * @return array
     */
    protected function getShippingRuleData(): array
    {
        return Yaml::parse(file_get_contents(__DIR__.'/data/shipping_methods_configs_rules_with_configs.yml'));
    }

    /**
     * @param string        $reference
     * @param array         $data
     * @param ObjectManager $manager
     */
    private function loadShippingRule($reference, $data, ObjectManager $manager)
    {
        $rule = $this->buildRule($reference, $data, $manager);
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
     * @return ShippingMethodsConfigsRule
     */
    protected function buildMethodsConfigsRule(
        string $reference,
        array $data,
        RuleInterface $rule,
        ObjectManager $manager
    ) {
        $configRule = new ShippingMethodsConfigsRule();

        $configRule
            ->setRule($rule)
            ->setCurrency($data['currency'])
            ->setOrganization($this->getOrganization());

        $this->setDestinations($configRule, $manager, $data);
        $this->setMethodConfigs($configRule, $manager, $data);

        return $configRule;
    }

    /**
     * @param string        $reference
     * @param array         $data
     * @param ObjectManager $manager
     *
     * @return Rule
     */
    protected function buildRule(string $reference, array $data, ObjectManager $manager)
    {
        $rule = new Rule();

        $rule->setName($reference)
            ->setEnabled($data['rule']['enabled'])
            ->setSortOrder($data['rule']['sortOrder'])
            ->setExpression($data['rule']['expression']);

        return $rule;
    }

    /**
     * @param ShippingMethodsConfigsRule $configRule
     * @param ObjectManager              $manager
     * @param array                      $data
     */
    private function setDestinations(ShippingMethodsConfigsRule $configRule, ObjectManager $manager, $data)
    {
        if (!array_key_exists('destinations', $data)) {
            return;
        }

        foreach ($data['destinations'] as $destination) {
            /** @var Country $country */
            $country = $manager
                ->getRepository('OroAddressBundle:Country')
                ->findOneBy(['iso2Code' => $destination['country']]);

            $shippingRuleDestination = new ShippingMethodsConfigsRuleDestination();
            $shippingRuleDestination
                ->setMethodConfigsRule($configRule)
                ->setCountry($country);

            if (array_key_exists('region', $destination)) {
                /** @var Region $region */
                $region = $manager
                    ->getRepository('OroAddressBundle:Region')
                    ->findOneBy(['combinedCode' => $destination['country'].'-'.$destination['region']]);
                $shippingRuleDestination->setRegion($region);
            }

            if (array_key_exists('postalCodes', $destination)) {
                foreach ($destination['postalCodes'] as $postalCode) {
                    $destinationPostalCode = new ShippingMethodsConfigsRuleDestinationPostalCode();
                    $destinationPostalCode->setName($postalCode['name'])
                        ->setDestination($shippingRuleDestination);

                    $shippingRuleDestination->addPostalCode($destinationPostalCode);
                }
            }

            $manager->persist($shippingRuleDestination);
            $configRule->addDestination($shippingRuleDestination);
        }
    }

    /**
     * @param ShippingMethodsConfigsRule $configRule
     * @param ObjectManager              $manager
     * @param array                      $data
     */
    private function setMethodConfigs(ShippingMethodsConfigsRule $configRule, ObjectManager $manager, $data)
    {
        if (!array_key_exists('methodConfigs', $data)) {
            return;
        }

        foreach ($data['methodConfigs'] as $methodConfigData) {
            $methodConfig = $this->buildMethodConfig($configRule);

            foreach ($methodConfigData['typeConfigs'] as $typeConfigData) {
                $typeConfig = new ShippingMethodTypeConfig();
                $typeConfig->setType('primary')
                    ->setOptions([
                        'price' => $typeConfigData['options']['price'],
                        'handling_fee' => null,
                        'type' => $typeConfigData['options']['type'],
                    ]);
                $typeConfig->setEnabled($typeConfigData['enabled']);
                $methodConfig->addTypeConfig($typeConfig);
            }

            $configRule->addMethodConfig($methodConfig);

            $manager->persist($methodConfig);
        }
    }

    /**
     * @param ShippingMethodsConfigsRule $configRule
     *
     * @return ShippingMethodConfig
     */
    private function buildMethodConfig(ShippingMethodsConfigsRule $configRule)
    {
        $methodConfig = new ShippingMethodConfig();

        $methodConfig
            ->setMethodConfigsRule($configRule)
            ->setMethod($this->getManualShippingIdentifier());

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
