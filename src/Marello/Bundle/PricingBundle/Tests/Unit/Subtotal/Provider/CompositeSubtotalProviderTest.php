<?php

namespace Marello\Bundle\PricingBundle\Tests\Unit\Subtotal\Provider;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Contracts\Translation\TranslatorInterface;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;
use Oro\Bundle\CurrencyBundle\Provider\DefaultCurrencyProviderInterface;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;
use Marello\Bundle\PricingBundle\Subtotal\Provider\CompositeSubtotalProvider;
use Marello\Bundle\PricingBundle\Subtotal\Provider\SubtotalProviderInterface;

class CompositeSubtotalProviderTest extends TestCase
{
    /**
     * @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $translator;

    /**
     * @var RoundingServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rounding;

    /**
     * @var DefaultCurrencyProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $defaultCurrencyProvider;

    /**
     * @var CompositeSubtotalProvider
     */
    protected $compositeSubtotalProvider;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->rounding = $this->createMock(RoundingServiceInterface::class);
        $this->defaultCurrencyProvider = $this->createMock(DefaultCurrencyProviderInterface::class);
        $this->compositeSubtotalProvider = new CompositeSubtotalProvider(
            $this->translator,
            $this->rounding,
            $this->defaultCurrencyProvider
        );
    }

    public function testGetName()
    {
        static::assertEquals(CompositeSubtotalProvider::NAME, $this->compositeSubtotalProvider->getName());
    }

    public function testGetSubtotal()
    {
        $provider1 = $this->createProviderMock('subtotal1', [], true);
        $provider2 = $this->createProviderMock('subtotal2', [], true);

        $this->compositeSubtotalProvider->addProvider($provider1);
        $this->compositeSubtotalProvider->addProvider($provider2);

        $subtotals = $this->compositeSubtotalProvider->getSubtotal(new Order());
        $this->assertInstanceOf(ArrayCollection::class, $subtotals);
    }

    /**
     * @dataProvider totalDataProvider
     *
     * @param array $subtotalData1
     * @param array $subtotalData2
     * @param array $totalData
     */
    public function testGetTotal(array $subtotalData1, array $subtotalData2, array $totalData)
    {
        $provider1 = $this->createProviderMock('subtotal1', $subtotalData1, true);
        $provider2 = $this->createProviderMock('subtotal2', $subtotalData2, true);

        $this->compositeSubtotalProvider->addProvider($provider1);
        $this->compositeSubtotalProvider->addProvider($provider2);

        $this->defaultCurrencyProvider
            ->expects(static::any())
            ->method('getDefaultCurrency')
            ->willReturn('USD');
        
        $this->translator
            ->expects(static::once())
            ->method('trans')
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $this->rounding
            ->expects(static::once())
            ->method('round')
            ->willReturnCallback(function ($value) {
                return $value;
            });

        static::assertEquals(new Subtotal($totalData), $this->compositeSubtotalProvider->getTotal(new Order()));
    }

    /**
     * @dataProvider totalDataProvider
     *
     * @param array $subtotalData1
     * @param array $subtotalData2
     * @param array $totalData
     */
    public function testGetTotalWithProvidedSubtotal(array $subtotalData1, array $subtotalData2, array $totalData)
    {
        $this->defaultCurrencyProvider
            ->expects(static::any())
            ->method('getDefaultCurrency')
            ->willReturn('USD');

        $this->translator
            ->expects(static::once())
            ->method('trans')
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $this->rounding
            ->expects(static::once())
            ->method('round')
            ->willReturnCallback(function ($value) {
                return $value;
            });

        static::assertEquals(
            new Subtotal($totalData),
            $this->compositeSubtotalProvider->getTotal(new Order(), [new Subtotal($subtotalData1), new Subtotal($subtotalData2)])
        );
    }

    public function totalDataProvider()
    {
        return [
            'adding' => [
                'subtotalData1' => [
                    Subtotal::OPERATION_FIELD => Subtotal::OPERATION_ADD,
                    Subtotal::AMOUNT_FIELD => 100.0,
                    Subtotal::CURRENCY_FIELD => 'USD'
                ],
                'subtotalData2' => [
                    Subtotal::OPERATION_FIELD => Subtotal::OPERATION_ADD,
                    Subtotal::AMOUNT_FIELD => 50.0,
                    Subtotal::CURRENCY_FIELD => 'USD'
                ],
                'totalData' => [
                    Subtotal::OPERATION_FIELD => Subtotal::OPERATION_ADD,
                    Subtotal::AMOUNT_FIELD => 150.0,
                    Subtotal::CURRENCY_FIELD => 'USD',
                    Subtotal::TYPE_FIELD => 'total',
                    Subtotal::LABEL_FIELD => 'marello.pricing.subtotals.total.label',
                    Subtotal::VISIBLE_FIELD =>  true
                ]
            ],
            'substitution' => [
                'subtotalData1' => [
                    Subtotal::OPERATION_FIELD => Subtotal::OPERATION_ADD,
                    Subtotal::AMOUNT_FIELD => 100.0,
                    Subtotal::CURRENCY_FIELD => 'USD'
                ],
                'subtotalData2' => [
                    Subtotal::OPERATION_FIELD => Subtotal::OPERATION_SUBTRACTION,
                    Subtotal::AMOUNT_FIELD => 50.0,
                    Subtotal::CURRENCY_FIELD => 'USD'
                ],
                'totalData' => [
                    Subtotal::OPERATION_FIELD => Subtotal::OPERATION_ADD,
                    Subtotal::AMOUNT_FIELD => 50.0,
                    Subtotal::CURRENCY_FIELD => 'USD',
                    Subtotal::TYPE_FIELD => 'total',
                    Subtotal::LABEL_FIELD => 'marello.pricing.subtotals.total.label',
                    Subtotal::VISIBLE_FIELD =>  true
                ]
            ],
        ];
    }

    public function testIsSupported()
    {
        $entity = '';
        static::assertTrue($this->compositeSubtotalProvider->isSupported($entity));
    }

    public function testAddHasGetProviders()
    {
        $provider = $this->createProviderMock('provider', [], true);

        $this->compositeSubtotalProvider->addProvider($provider);

        static::assertEquals(['provider' => $provider], $this->compositeSubtotalProvider->getProviders());
        static::assertEquals($provider, $this->compositeSubtotalProvider->getProviderByName('provider'));
        static::assertTrue($this->compositeSubtotalProvider->hasProvider('provider'));
        static::assertFalse($this->compositeSubtotalProvider->hasProvider('wrong_provider'));
    }

    public function testGetSupportedProviders()
    {
        $provider1 = $this->createProviderMock('subtotal1', [], true);
        $provider2 = $this->createProviderMock('subtotal2', [], false);

        $this->compositeSubtotalProvider->addProvider($provider1);
        $this->compositeSubtotalProvider->addProvider($provider2);
        static::assertEquals([$provider1], $this->compositeSubtotalProvider->getSupportedProviders(''));
    }

    /**
     * @param string $name
     * @param array $subtotalData
     * @param boolean $isSupported
     * @return SubtotalProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProviderMock($name, array $subtotalData, $isSupported)
    {
        /** @var SubtotalProviderInterface|\PHPUnit\Framework\MockObject\MockObject $provider **/
        $provider = $this->createMock(SubtotalProviderInterface::class);
        $provider->expects(static::any())
            ->method('getName')
            ->willReturn($name);
        $provider->expects(static::any())
            ->method('isSupported')
            ->willReturn($isSupported);
        $provider->expects(static::any())
            ->method('getSubtotal')
            ->willReturn(new Subtotal($subtotalData));

        return $provider;
    }
}
