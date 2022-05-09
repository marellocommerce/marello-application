<?php

namespace Marello\Bundle\ShippingBundle\Tests\Functional\Entity\Repository;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Marello\Bundle\ShippingBundle\Tests\Functional\DataFixtures\LoadShippingMethodsConfigsRulesWithConfigs;
use Marello\Bundle\ShippingBundle\Tests\Functional\Helper\ManualShippingIntegrationTrait;
use Marello\Bundle\ShippingBundle\Tests\Unit\Provider\Stub\ShippingAddressStub;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\Testing\Unit\EntityTrait;

class ShippingMethodsConfigsRuleRepositoryTest extends WebTestCase
{
    use EntityTrait;
    use ManualShippingIntegrationTrait;

    /**
     * @var ShippingMethodsConfigsRuleRepository
     */
    protected $repository;

    /**
     * @var EntityManager
     */
    protected $em;

    protected function setUp(): void
    {
        $this->initClient([], static::generateBasicAuthHeader());
        $this->client->useHashNavigation(true);

        $this->loadFixtures([
            LoadShippingMethodsConfigsRulesWithConfigs::class,
        ]);

        $this->em = static::getContainer()->get('doctrine')
            ->getManagerForClass('MarelloShippingBundle:ShippingMethodsConfigsRule');
        $this->repository = $this->em->getRepository('MarelloShippingBundle:ShippingMethodsConfigsRule');
    }

    /**
     * @param array $entities
     *
     * @return array
     */
    private function getEntitiesIds(array $entities)
    {
        return array_map(function ($entity) {
            return $entity->getId();
        }, $entities);
    }

    /**
     * @dataProvider getByDestinationAndCurrencyDataProvider
     *
     * @param array                        $shippingAddressData
     * @param string                       $currency
     * @param ShippingMethodsConfigsRule[] $expectedRules
     */
    public function testGetByDestinationAndCurrency(array $shippingAddressData, $currency, array $expectedRules)
    {
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        $expectedRulesIds = $this->getEntitiesIds($this->getEntitiesByReferences($expectedRules));
        $actualRules = $this->repository->getByDestinationAndCurrency(
            $this->createShippingAddress($shippingAddressData),
            $currency,
            $aclHelper
        );

        $this->assertEquals($expectedRulesIds, $this->getEntitiesIds($actualRules));
    }

    /**
     * @return array
     */
    public function getByDestinationAndCurrencyDataProvider()
    {
        return [
            [
                'shippingAddress' => [
                    'country' => 'US',
                    'region' => [
                        'combinedCode' => 'US-NY',
                        'code' => 'NY',
                    ],
                    'postalCode' => '12345',
                ],
                'currency' => 'EUR',
                'expectedRulesIds' => [
                    'shipping_rule.1',
                    'shipping_rule.2',
                    'shipping_rule.3',
                    'shipping_rule.4',
                    'shipping_rule.5',
                ]
            ],
        ];
    }

    public function testGetByCurrencyWithoutDestination()
    {
        $currency = 'UAH';
        $expectedRules = $this->getEntitiesByReferences([
            'shipping_rule.10',
            'shipping_rule.11'
        ]);

        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        $actualRules = $this->repository->getByCurrencyWithoutDestination($currency, $aclHelper);

        $this->assertEquals($this->getEntitiesIds($expectedRules), $this->getEntitiesIds($actualRules));
    }

    public function testGetRulesWithoutShippingMethods()
    {
        $rulesWithoutShippingMethods = $this->repository->getRulesWithoutShippingMethods();
        $enabledRulesWithoutShippingMethods = $this->repository->getRulesWithoutShippingMethods(true);

        static::assertCount(4, $rulesWithoutShippingMethods);
        static::assertCount(3, $enabledRulesWithoutShippingMethods);
    }

    public function testDisableRulesWithoutShippingMethods()
    {
        $this->repository->disableRulesWithoutShippingMethods();

        $rulesWithoutShippingMethods = $this->repository->getRulesWithoutShippingMethods();
        $enabledRulesWithoutShippingMethods = $this->repository->getRulesWithoutShippingMethods(true);

        static::assertCount(4, $rulesWithoutShippingMethods);
        static::assertCount(0, $enabledRulesWithoutShippingMethods);
    }

    public function testGetRulesByMethod()
    {
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        $rulesByExistingMethod = $this->repository->getRulesByMethod($this->getManualShippingIdentifier(), $aclHelper);

        $expectedRuleReferences = [
            'shipping_rule.1',
            'shipping_rule.2',
            'shipping_rule.3',
            'shipping_rule.4',
            'shipping_rule.5',
            'shipping_rule.6',
            'shipping_rule.7',
            'shipping_rule.9',
            'shipping_rule_without_type_configs',
            'shipping_rule_with_disabled_type_configs',
        ];
        foreach ($expectedRuleReferences as $expectedRuleReference) {
            static::assertContains($this->getReference($expectedRuleReference), $rulesByExistingMethod);
        }

        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        $rulesByNotExistingMethod = $this->repository->getRulesByMethod('not_existing_method', $aclHelper);
        static::assertCount(0, $rulesByNotExistingMethod);
    }

    /**
     * @dataProvider getEnabledRulesByMethodDataProvider
     *
     * @param string[] $expectedRuleReferences
     */
    public function testGetEnabledRulesByMethod(array $expectedRuleReferences)
    {
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        $actualRules = $this->repository->getEnabledRulesByMethod($this->getManualShippingIdentifier(), $aclHelper);

        foreach ($expectedRuleReferences as $expectedRuleReference) {
            static::assertContains($this->getReference($expectedRuleReference), $actualRules);
        }
    }

    /**
     * @return array
     */
    public function getEnabledRulesByMethodDataProvider()
    {
        return [
            [
                'expectedRuleReferences' => [
                    'shipping_rule.1',
                    'shipping_rule.2',
                    'shipping_rule.4',
                    'shipping_rule.5',
                    'shipping_rule.6',
                    'shipping_rule.7',
                    'shipping_rule.9',
                    'shipping_rule_without_type_configs',
                    'shipping_rule_with_disabled_type_configs',
                ]
            ]
        ];
    }

    /**
     * @param array $rules
     *
     * @return array
     */
    protected function getEntitiesByReferences(array $rules)
    {
        return array_map(function ($ruleReference) {
            return $this->getReference($ruleReference);
        }, $rules);
    }

    /**
     * @param array $data
     *
     * @return AddressInterface|object
     */
    protected function createShippingAddress(array $data)
    {
        return $this->getEntity(ShippingAddressStub::class, [
            'country' => new Country($data['country']),
            'region' => $this->getEntity(
                Region::class,
                [
                    'code' => $data['region']['code'],
                ],
                [
                    'combinedCode' => $data['region']['combinedCode'],
                ]
            ),
            'postalCode' => $data['postalCode'],
        ]);
    }

    public function testGetByCurrency()
    {
        $expectedRules = $this->getEntitiesByReferences([
            'shipping_rule.10',
            'shipping_rule.11',
            'shipping_rule.12'
        ]);

        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        $this->assertEquals(
            $this->getEntitiesIds($expectedRules),
            $this->getEntitiesIds($this->repository->getByCurrency('UAH', $aclHelper))
        );
    }

    public function testGetByCurrencyWhenCurrencyNotExists()
    {
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        $this->assertEmpty($this->repository->getByCurrency('WON', $aclHelper));
    }
}
