<?php

namespace Marello\Bundle\PaymentBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodConfig;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Symfony\Component\Yaml\Yaml;

class LoadPaymentMethodConfigsWithFakeMethods extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            LoadPaymentMethodsConfigsRules::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getPaymentMethodConfigsData() as $reference => $data) {
            $this->loadPaymentMethodConfig($reference, $data, $manager);
        }

        $manager->flush();
    }

    /**
     * @return array
     */
    protected function getPaymentMethodConfigsData()
    {
        return Yaml::parse(file_get_contents(__DIR__.'/data/payment_method_configs_with_fake_methods.yml'));
    }

    /**
     * @param string        $reference
     * @param array         $data
     * @param ObjectManager $manager
     */
    private function loadPaymentMethodConfig($reference, $data, ObjectManager $manager)
    {
        $methodsConfigsRule = $this->getPaymentMethodsConfigsRule($data['methods_configs_rule']);

        $methodConfig = $this->createMethodConfig($methodsConfigsRule, $data['method']);

        $manager->persist($methodConfig);

        $this->setReference($reference, $methodConfig);
    }

    /**
     * @param PaymentMethodsConfigsRule $configsRule
     * @param string                     $method
     *
     * @return PaymentMethodConfig
     */
    private function createMethodConfig(PaymentMethodsConfigsRule $configsRule, $method)
    {
        $methodConfig = new PaymentMethodConfig();

        return $methodConfig->setMethodsConfigsRule($configsRule)
            ->setMethod($method);
    }

    /**
     * @param string $reference
     *
     * @return PaymentMethodsConfigsRule|object
     */
    private function getPaymentMethodsConfigsRule($reference)
    {
        return $this->getReference($reference);
    }
}
