<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Provider\OrderItem;

use Marello\Bundle\OrderBundle\Provider\OrderItem\OrderItemDataProviderInterface;
use Marello\Bundle\OrderBundle\Provider\OrderItem\CompositeOrderItemDataProvider;
use Symfony\Component\Translation\TranslatorInterface;

class CompositeOrderItemDataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translator;

    /**
     * @var CompositeOrderItemDataProvider
     */
    protected $compositeOrderItemDataProvider;

    protected function setUp()
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->compositeOrderItemDataProvider = new CompositeOrderItemDataProvider($this->translator);
    }

    /**
     * @dataProvider getDataDataProvider
     *
     * @param array $provider1Data
     * @param array $provider2Data
     * @param array $expectedData
     */
    public function testGetData(array $provider1Data, array $provider2Data, array $expectedData)
    {
        $channelId = 1;
        $products = [
            ['product' => 1],
            ['product' => 2],
            ['product' => 3]
        ];

        $this->translator->expects(static::any())
            ->method('trans')
            ->willReturn('message');

        $this->compositeOrderItemDataProvider->addProvider('type1', $this->createProviderMock($provider1Data));
        $this->compositeOrderItemDataProvider->addProvider('type2', $this->createProviderMock($provider2Data));

        static::assertEquals($expectedData, $this->compositeOrderItemDataProvider->getData($channelId, $products));
    }

    /**
     * @param $data
     * @return OrderItemDataProviderInterface|\PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    protected function createProviderMock($data)
    {
        $provider = $this->createMock(OrderItemDataProviderInterface::class);
        $provider
            ->expects(static::once())
            ->method('getData')
            ->willReturn($data);

        return $provider;
    }

    /**
     * @param int $id
     * @return string
     */
    protected function getIdentifier($id)
    {
        return sprintf('%s%s', OrderItemDataProviderInterface::IDENTIFIER_PREFIX, $id);
    }

    /**
     * @param int $count
     * @param string $key
     * @return array
     */
    protected function getProviderData($count, $key)
    {
        $data = [];
        for ($i = 1; $i <= $count; $i++) {
            $data[$this->getIdentifier($i)] = [$key => $i];
        }

        return $data;
    }

    /**
     * @param int $count
     * @return array
     */
    protected function getCompositeData($count)
    {
        $keys = ['type1' => 'value', 'type2' => 'code'];
        $data = [];
        for ($i = 1; $i <= $count; $i++) {
            foreach ($keys as $type => $key) {
                $data[$this->getIdentifier($i)][$type][$key] = $i;
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getDataDataProvider()
    {
        $notSellableData = $this->getCompositeData(2);
        $notSellableData[$this->getIdentifier(3)] = ['message' => 'message'];

        return [
            'allProductsValid' => [
                'provider1Data' => $this->getProviderData(3, 'value'),
                'provider2Data' => $this->getProviderData(3, 'code'),
                'expectedData' => $this->getCompositeData(3),
            ],
            'notAllProductsValid' => [
                'provider1Data' => $this->getProviderData(3, 'value'),
                'provider2Data' => $this->getProviderData(2, 'code'),
                'expectedData' => $notSellableData,
            ],
            'AllProductsNotValid' => [
                'provider1Data' => [],
                'provider2Data' => [],
                'expectedData' => [
                    $this->getIdentifier(1) => ['message' => 'message'],
                    $this->getIdentifier(2) => ['message' => 'message'],
                    $this->getIdentifier(3) => ['message' => 'message']
                ]
            ]
        ];
    }
}
