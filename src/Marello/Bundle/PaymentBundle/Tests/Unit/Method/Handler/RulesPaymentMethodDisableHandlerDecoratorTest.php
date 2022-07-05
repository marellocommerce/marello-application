<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Method\Handler;

use Marello\Bundle\RuleBundle\Entity\Rule;
use Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodConfig;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Marello\Bundle\PaymentBundle\Method\Handler\RulesPaymentMethodDisableHandlerDecorator;
use Marello\Bundle\PaymentBundle\Method\Handler\PaymentMethodDisableHandlerInterface;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class RulesPaymentMethodDisableHandlerDecoratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentMethodDisableHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $handler;

    /**
     * @var PaymentMethodsConfigsRuleRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $repository;

    /**
     * @var PaymentMethodProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentMethodProvider;

    /**
     * @var AclHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $aclHelper;

    /**
     * @var RulesPaymentMethodDisableHandlerDecorator
     */
    protected $decorator;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->handler = $this->createMock(PaymentMethodDisableHandlerInterface::class);
        $this->repository = $this->createMock(PaymentMethodsConfigsRuleRepository::class);
        $this->paymentMethodProvider = $this->createMock(PaymentMethodProviderInterface::class);
        $this->aclHelper = $this->createMock(AclHelper::class);

        $this->decorator = new RulesPaymentMethodDisableHandlerDecorator(
            $this->handler,
            $this->repository,
            $this->paymentMethodProvider,
            $this->aclHelper
        );
    }

    /**
     * @param string $disabledMethodId
     * @param array  $configs
     * @param array  $registryMap
     *
     * @dataProvider testHandleMethodDisableProvider
     */
    public function testHandleMethodDisable($disabledMethodId, $configs, $registryMap)
    {
        $this->handler->expects(self::once())->method('handleMethodDisable')->with($disabledMethodId);

        $configMocks = [];
        $registryMapValues = [];
        $methods = [];
        foreach ($registryMap as $methodId => $enabled) {
            $methods[$methodId] = $this->createMock(PaymentMethodInterface::class);
            $methods[$methodId]->expects(self::any())->method('isEnabled')->willReturn($enabled);
            $registryMapValues[] = [$methodId, $methods[$methodId]];
        }

        $rules = [];
        foreach ($configs as $configName => $config) {
            $methodConfigs = [];
            foreach ($config['methods'] as $methodId) {
                $methodConfig = $this->createMock(PaymentMethodConfig::class);
                $methodConfig->expects(self::once())->method('getMethod')->willReturn($methodId);
                $methodConfigs[] =  $methodConfig;
            }
            $rules[$configName] = $this->createMock(Rule::class);
            $rules[$configName]->expects(self::exactly($config['rule_disabled']))->method('setEnabled')->with(false);

            $configMock = $this->createMock(PaymentMethodsConfigsRule::class);
            $configMock->expects(self::once())
                ->method('getMethodConfigs')
                ->willReturn($methodConfigs);
            $configMock->expects(self::any())
                ->method('getRule')
                ->willReturn($rules[$configName]);
            $configMocks[] = $configMock;
        }

        $this->paymentMethodProvider
            ->method('getPaymentMethod')
            ->will($this->returnValueMap($registryMapValues));

        $this->repository->expects(self::once())
             ->method('getEnabledRulesByMethod')
             ->willReturn($configMocks);

        $this->decorator->handleMethodDisable($disabledMethodId);
    }

    /**
     * @return array
     */
    public function testHandleMethodDisableProvider()
    {
        return [
            'a_few_methods' =>
                [
                    'methodId' => 'method1',
                    'configs' =>
                        [
                            'config1' =>
                                [
                                    'methods' => ['method1', 'method2'],
                                    'rule_disabled' => 1,
                                ],
                            'config2' =>
                                [
                                    'methods' => ['method1', 'method3'],
                                    'rule_disabled' => 0,
                                ]
                        ],
                    'registry_map' =>
                        [
                            'method1' => true,
                            'method2' => false,
                            'method3' => true,
                        ],
                ],
            'only_method' =>
                [
                    'methodId' => 'method1',
                    'configs' =>
                        [
                            'config1' =>
                                [
                                    'methods' => ['method1'],
                                    'rule_disabled' => 1,
                                ],
                        ],
                    'registry_map' =>
                        [
                            'method1' => true,
                        ],
                ],
        ];
    }
}
