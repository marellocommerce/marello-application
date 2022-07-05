<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Provider\MethodsConfigsRule\Context\Basic;

use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository;
use Marello\Bundle\ShippingBundle\Provider\MethodsConfigsRule\Context\Basic\BasicMethodsConfigsRulesByContextProvider;
use Marello\Bundle\ShippingBundle\RuleFiltration\MethodsConfigsRulesFiltrationServiceInterface;
use Marello\Bundle\ShippingBundle\Tests\Unit\Context\ShippingContextMockTrait;
use Marello\Bundle\ShippingBundle\Tests\Unit\Entity\ShippingMethodsConfigsRuleMockTrait;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class BasicMethodsConfigsRulesByContextProviderTest extends \PHPUnit\Framework\TestCase
{
    use ShippingContextMockTrait;
    use ShippingMethodsConfigsRuleMockTrait;

    /**
     * @var ShippingMethodsConfigsRuleRepository|\PHPUnit\Framework\MockObject\MockObject
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
     * @var BasicMethodsConfigsRulesByContextProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ShippingMethodsConfigsRuleRepository::class);
        $this->filtrationService = $this->createMock(MethodsConfigsRulesFiltrationServiceInterface::class);
        $this->aclHelper = $this->createMock(AclHelper::class);

        $this->provider = new BasicMethodsConfigsRulesByContextProvider(
            $this->filtrationService,
            $this->repository,
            $this->aclHelper
        );
    }

    public function testGetAllFilteredShippingMethodsConfigsWithShippingAddress()
    {
        $currency = 'USD';
        $address = $this->createAddressMock();
        $rulesFromDb = [
            $this->createShippingMethodsConfigsRuleMock(),
            $this->createShippingMethodsConfigsRuleMock(),
        ];

        $this->repository->expects(static::once())
            ->method('getByDestinationAndCurrency')
            ->with($address, $currency)
            ->willReturn($rulesFromDb);

        $this->repository->expects(static::never())
            ->method('getByCurrencyWithoutDestination');

        $context = $this->createShippingContextMock();
        $context->method('getCurrency')
            ->willReturn($currency);
        $context->method('getShippingAddress')
            ->willReturn($address);

        $expectedRules = [$this->createShippingMethodsConfigsRuleMock()];

        $this->filtrationService->expects(static::once())
            ->method('getFilteredShippingMethodsConfigsRules')
            ->with($rulesFromDb)
            ->willReturn($expectedRules);

        static::assertSame(
            $expectedRules,
            $this->provider->getShippingMethodsConfigsRules($context)
        );
    }

    public function testGetAllFilteredShippingMethodsConfigsWithoutShippingAddress()
    {
        $currency = 'USD';
        $rulesFromDb = [$this->createShippingMethodsConfigsRuleMock()];

        $this->repository->expects(static::once())
            ->method('getByCurrencyWithoutDestination')
            ->with($currency)
            ->willReturn($rulesFromDb);

        $context = $this->createShippingContextMock();
        $context->method('getCurrency')
            ->willReturn($currency);

        $expectedRules = [$this->createShippingMethodsConfigsRuleMock()];

        $this->filtrationService->expects(static::once())
            ->method('getFilteredShippingMethodsConfigsRules')
            ->with($rulesFromDb)
            ->willReturn($expectedRules);

        static::assertSame(
            $expectedRules,
            $this->provider->getShippingMethodsConfigsRules($context)
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
