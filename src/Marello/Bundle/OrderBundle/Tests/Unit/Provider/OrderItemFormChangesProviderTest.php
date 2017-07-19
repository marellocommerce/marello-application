<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Provider;

use Marello\Bundle\OrderBundle\Provider\OrderItem\OrderItemDataProviderInterface;
use Marello\Bundle\OrderBundle\Provider\OrderItemFormChangesProvider;
use Symfony\Component\Form\FormInterface;

class OrderItemFormChangesProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrderItemDataProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemDataProvider;

    /**
     * @var OrderItemFormChangesProvider
     */
    protected $orderItemFormChangesProvider;

    protected function setUp()
    {
        $this->orderItemDataProvider = $this->createMock(OrderItemDataProviderInterface::class);
        $this->orderItemFormChangesProvider = new OrderItemFormChangesProvider($this->orderItemDataProvider);
    }

    /**
     * @dataProvider getFormChangesDataProvider
     *
     * @param bool $formHasItems
     * @param array $submitData
     * @param array|null $expectedData
     */
    public function testGetFormChangesData($formHasItems, array $submitData, array $expectedData = null)
    {
        /** @var FormInterface|\PHPUnit_Framework_MockObject_MockObject $form **/
        $form = $this->createMock(FormInterface::class);
        $form->expects(static::once())
            ->method('has')
            ->with(OrderItemFormChangesProvider::ITEMS_FIELD)
            ->willReturn($formHasItems);

        $this->orderItemDataProvider->expects(static::any())
            ->method('getData')
            ->willReturn(['itemData1', 'itemData2', 'itemData3']);

        static::assertEquals(
            $expectedData,
            $this->orderItemFormChangesProvider->getFormChangesData($form, $submitData)
        );
    }

    public function getFormChangesDataProvider()
    {
        return [
            'validData' => [
                'formHasItems' => true,
                'submitData' => [
                    OrderItemFormChangesProvider::CHANNEL_FIELD => 1,
                    OrderItemFormChangesProvider::ITEMS_FIELD => [
                        ['product' => 1],
                        ['product' => 2],
                        ['product' => 3],
                    ],
                ],
                'expectedData' => ['itemData1', 'itemData2', 'itemData3'],
            ],
            'formDoesNotHaveItemsField' => [
                'formHasItems' => false,
                'submitData' => [
                    OrderItemFormChangesProvider::CHANNEL_FIELD => 1,
                    OrderItemFormChangesProvider::ITEMS_FIELD => [
                        ['product' => 1],
                        ['product' => 2],
                        ['product' => 3],
                    ],
                ],
                'expectedData' => null,
            ],
            'submitDataDoesNotHaveItems' => [
                'formHasItems' => true,
                'submitData' => [
                    OrderItemFormChangesProvider::CHANNEL_FIELD => 1
                ],
                'expectedData' => null,
            ],
            'submitDataDoesNotHaveSalesChannel' => [
                'formHasItems' => true,
                'submitData' => [
                    OrderItemFormChangesProvider::ITEMS_FIELD => [
                        ['product' => 1],
                        ['product' => 2],
                        ['product' => 3],
                    ],
                ],
                'expectedData' => null,
            ]
        ];
    }
}
