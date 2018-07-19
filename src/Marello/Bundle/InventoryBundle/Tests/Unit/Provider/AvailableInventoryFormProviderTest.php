<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Provider;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Symfony\Component\Form\FormInterface;

use Marello\Bundle\LayoutBundle\Context\FormChangeContext;
use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;
use Marello\Bundle\OrderBundle\Provider\OrderItem\OrderItemFormChangesProvider;
use Marello\Bundle\InventoryBundle\Provider\AvailableInventoryFormProvider;
use Marello\Bundle\InventoryBundle\Provider\AvailableInventoryProvider;

class AvailableInventoryFormProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AvailableInventoryFormProvider
     */
    protected $availableInventoryFormProvider;

    /**
     * @var FormChangeContextInterface
     */
    protected $context;

    /**
     * @var AvailableInventoryProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $inventoryProvider;

    protected function setUp()
    {
        $this->inventoryProvider = $this->createMock(AvailableInventoryProvider::class);
        $this->availableInventoryFormProvider = new AvailableInventoryFormProvider($this->inventoryProvider);
    }

    /**
     * @param int $id
     * @return string
     */
    protected function getIdentifier($id)
    {
        return sprintf('%s%s', OrderItemFormChangesProvider::IDENTIFIER_PREFIX, $id);
    }

    /**
     * @dataProvider processFormChangesDataProvider
     *
     * @param null|Order $formData
     * @param bool $hasItems
     * @param bool $isValid
     * @param array $submitData
     * @param array|null $expectedData
     */
    public function testProcessFormChanges(
        $formData,
        $hasItems,
        $isValid,
        array $submitData,
        array $expectedData
    ) {
        /** @var FormInterface|\PHPUnit_Framework_MockObject_MockObject $form **/
        $form = $this->createMock(FormInterface::class);
        $form->expects(static::once())
            ->method('getData')
            ->willReturn($formData);

        $this->context = new FormChangeContext([
            FormChangeContext::FORM_FIELD => $form,
            FormChangeContext::SUBMITTED_DATA_FIELD => $submitData,
            FormChangeContext::RESULT_FIELD => []
        ]);

        if ($hasItems) {
            $products = array_values($submitData['products']);
            $this->inventoryProvider->expects(static::atLeastOnce())
                ->method('getProducts')
                ->willReturn($products);

            /** @var \PHPUnit_Framework_MockObject_MockObject $product */
            foreach ($submitData['products'] as $id => $product) {
                if ($isValid) {
                    $this->inventoryProvider->expects(static::atLeastOnce())
                        ->method('getAvailableInventory')
                        ->willReturn($submitData['inventory'][$id]);
                }

                $product->expects(static::atLeastOnce())
                    ->method('getId')
                    ->willReturn($id);
            }
        }

        $this->availableInventoryFormProvider->processFormChanges($this->context);

        static::assertEquals(
            $expectedData,
            $this->context->getResult()
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOrderMock()
    {
        $orderMock = $this->createMock(Order::class);
        $orderMock->expects(static::atLeastOnce())
            ->method('getSalesChannel')
            ->willReturn($this->getSalesChannelMock());

        return $orderMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSalesChannelMock()
    {
        $salesChannelMock = $this->createMock(SalesChannel::class);
        $salesChannelMock->expects(self::atLeastOnce())
            ->method('getId')
            ->willReturn(1);

        return $salesChannelMock;
    }

    /**
     * @return array
     */
    public function processFormChangesDataProvider()
    {
        return [
            'formDataIsNotOfTypeOrder' => [
                'formData' => null,
                'hasItems' => false,
                'isValid' => true,
                'submitData' => [],
                'expectedData' => []
            ],
            'NoValidData' => [
                'formData' => $this->getOrderMock(),
                'hasItems' => true,
                'isValid' => true,
                'submitData' => [
                    'products' => [],
                    OrderItemFormChangesProvider::ITEMS_FIELD => [
                        []
                    ],
                ],
                'expectedData' => []
            ],
            'notAllProductsValid' => [
                'formData' => $this->getOrderMock(),
                'hasItems' => true,
                'isValid' => true,
                'submitData' => [
                    'products' => [
                        1 => $this->createMock(Product::class)
                    ],
                    'inventory' => [
                        1 => 10
                    ],
                    OrderItemFormChangesProvider::ITEMS_FIELD => [
                        [
                            AvailableInventoryFormProvider::PRODUCT_FIELD => 1
                        ],
                        [
                            AvailableInventoryFormProvider::PRODUCT_FIELD => 0
                        ]
                    ],
                ],
                'expectedData' => [
                    OrderItemFormChangesProvider::ITEMS_FIELD => [
                        AvailableInventoryFormProvider::INVENTORY_FIELD => [
                            $this->getIdentifier(1) => ['value' => 10]
                        ]
                    ]
                ]
            ],
            'allProductsAreValid' => [
                'formData' => $this->getOrderMock(),
                'hasItems' => true,
                'isValid' => true,
                'submitData' => [
                    'products' => [
                        1 => $this->createMock(Product::class),
                        2 => $this->createMock(Product::class)
                    ],
                    'inventory' => [
                        1 => 10,
                        2 => 10
                    ],
                    OrderItemFormChangesProvider::ITEMS_FIELD => [
                        [
                            AvailableInventoryFormProvider::PRODUCT_FIELD => 1
                        ],
                        [
                            AvailableInventoryFormProvider::PRODUCT_FIELD => 2
                        ],
                    ],
                ],
                'expectedData' => [
                    OrderItemFormChangesProvider::ITEMS_FIELD => [
                        AvailableInventoryFormProvider::INVENTORY_FIELD => [
                            $this->getIdentifier(1) => ['value' => 10],
                            $this->getIdentifier(2) => ['value' => 10]
                        ]
                    ]
                ]
            ]
        ];
    }
}
