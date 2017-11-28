<?php

namespace Marello\Bundle\PricingBundle\Tests\Unit\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\LayoutBundle\Context\FormChangeContext;
use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Provider\OrderItem\OrderItemFormChangesProvider;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\PricingBundle\Entity\Repository\ProductChannelPriceRepository;
use Marello\Bundle\PricingBundle\Provider\ChannelPriceProvider;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Form\FormInterface;

class ChannelPriceProviderTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var FormChangeContextInterface
     */
    protected $context;

    /**
     * @var ChannelPriceProvider
     */
    protected $channelPriceProvider;

    protected function setUp()
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->channelPriceProvider = new ChannelPriceProvider($this->registry);
    }

    /**
     * @dataProvider processFormChangesDataProvider
     *
     * @param array $channelPrices
     * @param ProductPrice $defaultPrice
     * @param int $expectedValue
     */
    public function testProcessFormChanges($channelPrices, ProductPrice $defaultPrice, $expectedValue)
    {
        /** @var SalesChannel $channel */
        $channel = $this->getEntity(SalesChannel::class, ['id' => 1, 'currency' => 'EUR']);
        /** @var Order $order */
        $order = $this->getEntity(Order::class, ['id' => 1, 'salesChannel' => $channel]);
        /** @var Product $product */
        $product = $this->getEntity(Product::class, ['id' => 1]);

        /** @var FormInterface|\PHPUnit_Framework_MockObject_MockObject $form **/
        $form = $this->createMock(FormInterface::class);
        $form->expects(static::once())
            ->method('getData')
            ->willReturn($order);

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository
            ->expects(static::once())
            ->method('findBySalesChannel')
            ->with($channel->getId(), [$product->getId()])
            ->willReturn([$product]);

        $productPriceRepository = $this->createMock(EntityRepository::class);
        $productPriceRepository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(['product' => $product->getId(), 'currency' => $channel->getCurrency()])
            ->willReturn($defaultPrice);

        $productChannelPriceRepository = $this->createMock(ProductChannelPriceRepository::class);
        $productChannelPriceRepository
            ->expects(static::once())
            ->method('findOneBySalesChannel')
            ->with($channel->getId(), $product->getId())
            ->willReturn($channelPrices);

        $this->registry
            ->expects(static::at(0))
            ->method('getManagerForClass')
            ->with(Product::class)
            ->willReturnSelf();
        $this->registry
            ->expects(static::at(1))
            ->method('getRepository')
            ->with(Product::class)
            ->willReturn($productRepository);

        $this->registry
            ->expects(static::at(2))
            ->method('getManagerForClass')
            ->with(ProductPrice::class)
            ->willReturnSelf();
        $this->registry
            ->expects(static::at(3))
            ->method('getRepository')
            ->with(ProductPrice::class)
            ->willReturn($productPriceRepository);

        $this->registry
            ->expects(static::at(4))
            ->method('getManagerForClass')
            ->with(ProductChannelPrice::class)
            ->willReturnSelf();
        $this->registry
            ->expects(static::at(5))
            ->method('getRepository')
            ->with(ProductChannelPrice::class)
            ->willReturn($productChannelPriceRepository);

        $expectedData = [
            'price' => [
                sprintf('%s%s', ChannelPriceProvider::IDENTIFIER_PREFIX, $product->getId()) => [
                    'value' => $expectedValue,
                ]
            ]
        ];

        $this->context = new FormChangeContext([
            FormChangeContext::FORM_FIELD => $form,
            FormChangeContext::SUBMITTED_DATA_FIELD => [
                OrderItemFormChangesProvider::ITEMS_FIELD => [['product' => $product->getId()]]
            ],
            FormChangeContext::RESULT_FIELD => []
        ]);

        $this->channelPriceProvider->processFormChanges($this->context);

        static::assertEquals(
            [OrderItemFormChangesProvider::ITEMS_FIELD => $expectedData],
            $this->context->getResult()
        );
    }

    public function processFormChangesDataProvider()
    {
        $defaultPrice = $this->getEntity(ProductPrice::class, ['id' => 1, 'value' => 100, 'currency' => 'EUR']);

        return [
            'noChannelPrice' => [
                'channelPrices' => [],
                'defaultPrice' => $defaultPrice,
                'expectedValue' => 100
            ],
            'withChannelPrice' => [
                'channelPrices' => [['price_value' => 50]],
                'defaultPrice' => $defaultPrice,
                'expectedValue' => 50
            ]
        ];
    }

    /**
     * @dataProvider getChannelPriceDataProvider
     *
     * @param array $prices
     * @param array $expectedData
     */
    public function testGetChannelPrice($prices, $expectedData)
    {
        /** @var SalesChannel $channel */
        $channel = $this->getEntity(SalesChannel::class, ['id' => 1, 'currency' => 'EUR']);
        /** @var Product $product */
        $product = $this->getEntity(Product::class, ['id' => 1]);
        $repository = $this->createMock(ProductChannelPriceRepository::class);

        $this->registry
            ->expects(static::once())
            ->method('getManagerForClass')
            ->with(ProductChannelPrice::class)
            ->willReturnSelf();
        $this->registry
            ->expects(static::once())
            ->method('getRepository')
            ->with(ProductChannelPrice::class)
            ->willReturn($repository);
        $repository
            ->expects(static::once())
            ->method('findOneBySalesChannel')
            ->with($channel->getId(), $product->getId())
            ->willReturn($prices);

        static::assertEquals($expectedData, $this->channelPriceProvider->getChannelPrice($channel, $product));
    }

    public function getChannelPriceDataProvider()
    {
        return [
            'noPrice' => [
                'prices' => [],
                'expectedData' => ['hasPrice' => false]
            ],
            'onePrice' => [
                'prices' => [['price_value' => 50]],
                'expectedData' => ['hasPrice' => true, 'price' => 50]
            ],
            'twoPrices' => [
                'prices' => [['price_value' => 50], ['price_value' => 60]],
                'expectedData' => ['hasPrice' => false]
            ]
        ];
    }

    /**
     * @dataProvider getDefaultPriceDataProvider
     *
     * @param mixed $price
     * @param int|null $expectedValue
     */
    public function testGetDefaultPrice($price, $expectedValue)
    {
        /** @var SalesChannel $channel */
        $channel = $this->getEntity(SalesChannel::class, ['id' => 1, 'currency' => 'EUR']);

        /** @var Product $product */
        $product = $this->getEntity(Product::class, ['id' => 1]);
        $productPriceRepository = $this->createMock(EntityRepository::class);
        $productPriceRepository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(['product' => $product->getId(), 'currency' => $channel->getCurrency()])
            ->willReturn($price);

        $this->registry
            ->expects(static::at(0))
            ->method('getManagerForClass')
            ->with(ProductPrice::class)
            ->willReturnSelf();
        $this->registry
            ->expects(static::at(1))
            ->method('getRepository')
            ->with(ProductPrice::class)
            ->willReturn($productPriceRepository);

        static::assertEquals($expectedValue, $this->channelPriceProvider->getDefaultPrice($channel, $product));
    }

    public function getDefaultPriceDataProvider()
    {
        return [
            'noPriceObject' => [
                'price' => [],
                'expectedValue' => null
            ],
            'correctPriceObject' => [
                'price' => $this->getEntity(ProductPrice::class, ['id' => 1, 'value' => 50, 'currency' => 'EUR']),
                'expectedValue' => 50
            ],
            'notCorrectPriceObject' => [
                'price' => $this->getEntity(Product::class, ['id' => 1]),
                'expectedValue' => null
            ]
        ];
    }
}
