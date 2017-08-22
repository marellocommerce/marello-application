<?php

namespace Marello\Bundle\PricingBundle\Tests\Unit\Provider;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Provider\OrderItemsSubtotalProvider;
use Marello\Bundle\PricingBundle\Provider\ChannelPriceProvider;
use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\CurrencyBundle\Provider\DefaultCurrencyProviderInterface;
use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Translation\TranslatorInterface;

class OrderItemsSubtotalProviderTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var OrderItemsSubtotalProvider
     */
    protected $provider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TranslatorInterface
     */
    protected $translator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RoundingServiceInterface
     */
    protected $roundingService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DefaultCurrencyProviderInterface
     */
    protected $defaultCurrencyProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ChannelPriceProvider
     */
    protected $channelPriceProvider;

    /**
     * @var SalesChannel[]
     */
    protected $salesChannels;

    protected function setUp()
    {
        parent::setUp();
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->defaultCurrencyProvider = $this->createMock(DefaultCurrencyProviderInterface::class);
        $this->channelPriceProvider = $this->createMock(ChannelPriceProvider::class);

        $this->roundingService = $this->createMock(RoundingServiceInterface::class);
        $this->roundingService->expects($this->any())
            ->method('round')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return round($value, 0, PHP_ROUND_HALF_UP);
                    }
                )
            );

        $this->salesChannels = [
            1 => $this->getEntity(SalesChannel::class, ['id' => 1]),
            2 => $this->getEntity(SalesChannel::class, ['id' => 2])
        ];

        $this->provider = new OrderItemsSubtotalProvider(
            $this->translator,
            $this->roundingService,
            $this->defaultCurrencyProvider,
            $this->channelPriceProvider
        );
    }

    /**
     * @dataProvider subtotalDataProvider
     *
     * @param array $items
     * @param int $salesChannel
     * @param float $expectedAmount
     */
    public function testGetSubtotal(array $items, $salesChannel, $expectedAmount)
    {
        $this->translator->expects($this->once())
            ->method('trans')
            ->with(OrderItemsSubtotalProvider::NAME . '.label')
            ->willReturn('test');

        $orderItems = [];
        $getChannelPriceRun = 0;
        $getChannelPriceReturn = [];
        $getDefaultPriceRun = 0;
        $getDefaultPriceReturn = [];
        foreach ($items as $id => $item) {
            $orderItems[] = $this->createOrderItem($id, $item);
            $getChannelPriceRun += $item['priceMethods']['getChannelPrice']['runTimes'];
            $getChannelPriceReturn[] = $item['priceMethods']['getChannelPrice']['returnValue'];
            $getDefaultPriceRun += $item['priceMethods']['getDefaultPrice']['runTimes'];
            $getDefaultPriceReturn[] = $item['priceMethods']['getDefaultPrice']['returnValue'];
        }

        $this->channelPriceProvider->expects(static::exactly($getChannelPriceRun))
            ->method('getChannelPrice')
            ->willReturnOnConsecutiveCalls($getChannelPriceReturn[0], $getChannelPriceReturn[1]);

        $this->channelPriceProvider->expects(static::exactly($getDefaultPriceRun))
            ->method('getDefaultPrice')
            ->willReturnOnConsecutiveCalls($getDefaultPriceReturn[0], $getDefaultPriceReturn[1]);

        /** @var Order $entity */
        $entity = $this->getEntity(Order::class, [
            'id' => 1,
            'salesChannel' => $this->salesChannels[$salesChannel],
            'currency' => 'USD',
            'items' => $orderItems
        ]);

        $subtotal = $this->provider->getSubtotal($entity);
        $this->assertInstanceOf(Subtotal::class, $subtotal);
        $this->assertEquals(OrderItemsSubtotalProvider::TYPE, $subtotal->getType());
        $this->assertEquals('test', $subtotal->getLabel());
        $this->assertEquals($entity->getCurrency(), $subtotal->getCurrency());
        $this->assertInternalType('float', $subtotal->getAmount());
        $this->assertEquals($expectedAmount, $subtotal->getAmount());
    }

    public function subtotalDataProvider()
    {
        return [
            [
                'items' => [
                    [
                        'price' => 20,
                        'priceMethods' => [
                            'getChannelPrice' => [
                                'runTimes' => 1,
                                'returnValue' => ['price' => 20]
                            ],
                            'getDefaultPrice' => [
                                'runTimes' => 0,
                                'returnValue' => 30
                            ],
                        ],
                        'quantity' => 2,
                        'product' => [
                            'channels' => [1, 2]
                        ],
                    ],
                    [
                        'price' => 10,
                        'priceMethods' => [
                            'getChannelPrice' => [
                                'runTimes' => 1,
                                'returnValue' => []
                            ],
                            'getDefaultPrice' => [
                                'runTimes' => 1,
                                'returnValue' => 30
                            ],
                        ],
                        'quantity' => 2,
                        'product' => [
                            'channels' => [1]
                        ],
                    ]
                ],
                'salesChannel' => 1,
                'expectedAmount' => 100
            ],
            [
                'items' => [
                    [
                        'price' => 20,
                        'priceMethods' => [
                            'getChannelPrice' => [
                                'runTimes' => 1,
                                'returnValue' => ['price' => 20]
                            ],
                            'getDefaultPrice' => [
                                'runTimes' => 0,
                                'returnValue' => 30
                            ],
                        ],
                        'quantity' => 2,
                        'product' => [
                            'channels' => [1, 2]
                        ],
                    ],
                    [
                        'price' => 10,
                        'priceMethods' => [
                            'getChannelPrice' => [
                                'runTimes' => 0,
                                'returnValue' => ['price' => 10]
                            ],
                            'getDefaultPrice' => [
                                'runTimes' => 0,
                                'returnValue' => 30
                            ],
                        ],
                        'quantity' => 2,
                        'product' => [
                            'channels' => [1]
                        ],
                    ]
                ],
                'salesChannel' => 2,
                'expectedAmount' => 40
            ]
        ];
    }

    public function testGetName()
    {
        $this->assertEquals(OrderItemsSubtotalProvider::NAME, $this->provider->getName());
    }

    public function testIsSupported()
    {
        $entity = new Order();
        $this->assertTrue($this->provider->isSupported($entity));
    }

    public function testIsNotSupported()
    {
        $entity = new OrderItem();
        $this->assertFalse($this->provider->isSupported($entity));
    }

    /**
     * @param int $id
     * @param array $item
     *
     * @return OrderItem
     */
    protected function createOrderItem($id, array $item)
    {
        /** @var Product $product */
        $product = $this->getEntity(Product::class, ['id' => $id]);
        foreach ($item['product']['channels'] as $channelId) {
            $product->addChannel($this->salesChannels[$channelId]);
        }

        return $orderItem = $this->getEntity(OrderItem::class, [
            'id' => $id,
            'product' => $product,
            'quantity' => $item['quantity'],
            'price' => $item['price']
        ]);
    }
}
