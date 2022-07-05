<?php

namespace Marello\Bundle\PaymentBundle\Tests\Functional\Entity\Repository;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository;
use Marello\Bundle\PaymentBundle\Tests\Functional\DataFixtures\LoadPaymentMethodsConfigsRulesWithConfigs;
use Marello\Bundle\PaymentBundle\Tests\Functional\Helper\PaymentTermIntegrationTrait;
use Marello\Bundle\ShippingBundle\Tests\Unit\Provider\Stub\ShippingAddressStub;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\Testing\Unit\EntityTrait;

class PaymentMethodsConfigsRuleRepositoryTest extends WebTestCase
{
    use EntityTrait;
    use PaymentTermIntegrationTrait;

    /**
     * @var PaymentMethodsConfigsRuleRepository
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
            LoadPaymentMethodsConfigsRulesWithConfigs::class,
        ]);

        $this->em = static::getContainer()->get('doctrine')
            ->getManagerForClass('MarelloPaymentBundle:PaymentMethodsConfigsRule');
        $this->repository = $this->em->getRepository('MarelloPaymentBundle:PaymentMethodsConfigsRule');
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
     * @param PaymentMethodsConfigsRule[] $expectedRules
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
                    'payment_rule.1',
                    'payment_rule.2',
                    'payment_rule.3',
                    'payment_rule.4',
                    'payment_rule.5',
                ]
            ],
        ];
    }

    public function testGetByCurrencyWithoutDestination()
    {
        $currency = 'UAH';
        $expectedRules = $this->getEntitiesByReferences([
            'payment_rule.10',
            'payment_rule.11'
        ]);

        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        $actualRules = $this->repository->getByCurrencyWithoutDestination($currency, $aclHelper);

        $this->assertEquals($this->getEntitiesIds($expectedRules), $this->getEntitiesIds($actualRules));
    }

    public function testGetRulesWithoutPaymentMethods()
    {
        $rulesWithoutPaymentMethods = $this->repository->getRulesWithoutPaymentMethods();
        $enabledRulesWithoutPaymentMethods = $this->repository->getRulesWithoutPaymentMethods(true);

        static::assertCount(4, $rulesWithoutPaymentMethods);
        static::assertCount(3, $enabledRulesWithoutPaymentMethods);
    }

    public function testDisableRulesWithoutPaymentMethods()
    {
        $this->repository->disableRulesWithoutPaymentMethods();

        $rulesWithoutPaymentMethods = $this->repository->getRulesWithoutPaymentMethods();
        $enabledRulesWithoutPaymentMethods = $this->repository->getRulesWithoutPaymentMethods(true);

        static::assertCount(4, $rulesWithoutPaymentMethods);
        static::assertCount(0, $enabledRulesWithoutPaymentMethods);
    }

    public function testGetRulesByMethod()
    {
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        $rulesByExistingMethod = $this->repository->getRulesByMethod($this->getPaymentTermIdentifier(), $aclHelper);

        $expectedRuleReferences = [
            'payment_rule.1',
            'payment_rule.2',
            'payment_rule.3',
            'payment_rule.4',
            'payment_rule.5',
            'payment_rule.6',
            'payment_rule.7',
            'payment_rule.9',
            'payment_rule_without_type_configs',
            'payment_rule_with_disabled_type_configs',
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
        $actualRules = $this->repository->getEnabledRulesByMethod($this->getPaymentTermIdentifier(), $aclHelper);

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
                    'payment_rule.1',
                    'payment_rule.2',
                    'payment_rule.4',
                    'payment_rule.5',
                    'payment_rule.6',
                    'payment_rule.7',
                    'payment_rule.9',
                    'payment_rule_without_type_configs',
                    'payment_rule_with_disabled_type_configs',
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
            'payment_rule.10',
            'payment_rule.11',
            'payment_rule.12'
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
