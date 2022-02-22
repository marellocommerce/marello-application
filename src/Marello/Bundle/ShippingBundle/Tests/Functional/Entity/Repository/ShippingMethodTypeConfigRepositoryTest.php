<?php

namespace Marello\Bundle\ShippingBundle\Tests\Functional\Entity\Repository;

use Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodTypeConfigRepository;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodConfig;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodTypeConfig;
use Marello\Bundle\ShippingBundle\Tests\Functional\DataFixtures\LoadShippingMethodsConfigsRulesWithConfigs;
use Marello\Bundle\ShippingBundle\Tests\Functional\Helper\ManualShippingIntegrationTrait;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ShippingMethodTypeConfigRepositoryTest extends WebTestCase
{
    use ManualShippingIntegrationTrait;

    /**
     * @var ShippingMethodTypeConfigRepository
     */
    protected $repository;

    protected function setUp(): void
    {
        $this->initClient([], static::generateBasicAuthHeader());
        $this->loadFixtures([
            LoadShippingMethodsConfigsRulesWithConfigs::class
        ]);

        $this->repository = static::getContainer()->get('doctrine')
            ->getRepository('MarelloShippingBundle:ShippingMethodTypeConfig');
    }

    public function testFindShippingMethodTypeConfigConfigsByMethodAndType()
    {
        $ids = $this->repository->findIdsByMethodAndType(
            $this->getManualShippingIdentifier(),
            $this->getManualShippingPrimaryIdentifier()
        );

        static::assertContains($this->getFirstTypeId('shipping_rule.1'), $ids);
        static::assertContains($this->getFirstTypeId('shipping_rule.2'), $ids);
    }

    /**
     * @param string $ruleReference
     * @return int
     */
    private function getFirstTypeId($ruleReference)
    {
        /** @var ShippingMethodConfig $methodConfig */
        $methodConfig = $this->getReference($ruleReference)->getMethodConfigs()->first();
        return $methodConfig->getTypeConfigs()->first()->getId();
    }

    public function testDeleteMethodConfigByIds()
    {
        $ids = [
            $this->getFirstTypeId('shipping_rule.1'),
            $this->getFirstTypeId('shipping_rule.2'),
        ];

        static::assertCount(2, $this->repository->findBy(['id' => $ids]));

        $this->repository->deleteByIds($ids);

        static::assertEmpty($this->repository->findBy(['id' => $ids]));
    }

    public function testFindEnabledByMethodIdentifier()
    {
        $method = $this->getManualShippingIdentifier();

        $actual = $this->repository->findEnabledByMethodIdentifier($method);

        static::assertContains($this->getFirstType('shipping_rule.4'), $actual);
        static::assertContains($this->getFirstType('shipping_rule.9'), $actual);
        static::assertNotContains($this->getFirstType('shipping_rule_without_type_configs'), $actual);
        static::assertNotContains($this->getFirstType('shipping_rule_with_disabled_type_configs'), $actual);
    }

    /**
     * @param string $ruleReference
     *
     * @return ShippingMethodTypeConfig
     */
    private function getFirstType($ruleReference)
    {
        /** @var ShippingMethodConfig $methodConfig */
        $methodConfig = $this->getReference($ruleReference)->getMethodConfigs()->first();

        return $methodConfig->getTypeConfigs()->first();
    }
}
