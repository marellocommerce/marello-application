<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Provider\MethodsConfigsRule\Context\RegardlessDestination;

use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository;
use Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\RegardlessDestination;
use Marello\Bundle\PaymentBundle\RuleFiltration\MethodsConfigsRulesFiltrationServiceInterface;
use Marello\Bundle\PaymentBundle\Tests\Unit\Context\PaymentContextMockTrait;
use Marello\Bundle\PaymentBundle\Tests\Unit\Entity\PaymentMethodsConfigsRuleMockTrait;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class RegardlessDestinationMethodsConfigsRulesByContextProviderTest extends \PHPUnit\Framework\TestCase
{
    use PaymentContextMockTrait;
    use PaymentMethodsConfigsRuleMockTrait;

    /**
     * @var PaymentMethodsConfigsRuleRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $repository;

    /**
     * @var MethodsConfigsRulesFiltrationServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filtrationService;

    /**
     * @var AclHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $aclHelper;

    /**
     * @var RegardlessDestination\RegardlessDestinationMethodsConfigsRulesByContextProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PaymentMethodsConfigsRuleRepository::class);
        $this->filtrationService = $this->createMock(MethodsConfigsRulesFiltrationServiceInterface::class);
        $this->aclHelper = $this->createMock(AclHelper::class);

        $this->provider = new RegardlessDestination\RegardlessDestinationMethodsConfigsRulesByContextProvider(
            $this->filtrationService,
            $this->repository,
            $this->aclHelper
        );
    }

    public function testGetAllFilteredPaymentMethodsConfigsWithBillingAddress()
    {
        $currency = 'USD';
        $address = $this->createAddressMock();
        $rulesFromDb = [$this->createPaymentMethodsConfigsRuleMock()];

        $this->repository->expects(static::once())
            ->method('getByDestinationAndCurrency')
            ->with($address, $currency)
            ->willReturn($rulesFromDb);

        $this->repository->expects(static::never())
            ->method('getByCurrency');

        $context = $this->createPaymentContextMock();
        $context->method('getCurrency')
            ->willReturn($currency);
        $context->method('getBillingAddress')
            ->willReturn($address);

        $expectedRules = [
            $this->createPaymentMethodsConfigsRuleMock(),
            $this->createPaymentMethodsConfigsRuleMock(),
        ];

        $this->filtrationService->expects(static::once())
            ->method('getFilteredPaymentMethodsConfigsRules')
            ->with($rulesFromDb)
            ->willReturn($expectedRules);

        static::assertSame(
            $expectedRules,
            $this->provider->getPaymentMethodsConfigsRules($context)
        );
    }

    public function testGetAllFilteredPaymentMethodsConfigsWithoutBillingAddress()
    {
        $currency = 'USD';
        $rulesFromDb = [$this->createPaymentMethodsConfigsRuleMock()];

        $this->repository->expects(static::once())
            ->method('getByCurrency')
            ->with($currency)
            ->willReturn($rulesFromDb);

        $context = $this->createPaymentContextMock();
        $context->method('getCurrency')
            ->willReturn($currency);

        $expectedRules = [$this->createPaymentMethodsConfigsRuleMock()];

        $this->filtrationService->expects(static::once())
            ->method('getFilteredPaymentMethodsConfigsRules')
            ->with($rulesFromDb)
            ->willReturn($expectedRules);

        static::assertSame(
            $expectedRules,
            $this->provider->getPaymentMethodsConfigsRules($context)
        );
    }

    /**
     * @return AddressInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createAddressMock()
    {
        return $this->createMock(AddressInterface::class);
    }
}
