<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Provider\OrderItem;

use Marello\Bundle\LayoutBundle\Context\FormChangeContext;
use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;
use Marello\Bundle\OrderBundle\Provider\OrderItem\OrderItemFormChangesProvider;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

class OrderItemFormChangesProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translator;

    /**
     * @var FormChangeContextInterface
     */
    protected $context;

    /**
     * @var OrderItemFormChangesProvider
     */
    protected $orderItemFormChangesProvider;

    protected function setUp()
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->orderItemFormChangesProvider = new OrderItemFormChangesProvider($this->translator);
    }

    /**
     * @param $data
     * @return FormChangesProviderInterface|\PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    protected function createProviderMock($data)
    {
        $provider = $this->createMock(FormChangesProviderInterface::class);
        $provider
            ->expects(static::any())
            ->method('processFormChanges')
            ->willReturnCallback(function () use ($data) {
                $result = $this->context->getResult();
                $result[OrderItemFormChangesProvider::ITEMS_FIELD][$data['type']] = $data['data'];
                $this->context->setResult($result);
            });

        return $provider;
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
     * @dataProvider processFormChangesDataProvider
     *
     * @param array $provider1Data
     * @param array $provider2Data
     * @param bool $formHasItems
     * @param array $submitData
     * @param array|null $expectedData
     */
    public function testProcessFormChanges(
        array $provider1Data,
        array $provider2Data,
        $formHasItems,
        array $submitData,
        array $expectedData
    ) {
        /** @var FormInterface|\PHPUnit_Framework_MockObject_MockObject $form **/
        $form = $this->createMock(FormInterface::class);
        $form->expects(static::once())
            ->method('has')
            ->with(OrderItemFormChangesProvider::ITEMS_FIELD)
            ->willReturn($formHasItems);

        $this->translator->expects(static::any())
            ->method('trans')
            ->willReturn('message');

        $this->context = new FormChangeContext([
            FormChangeContext::FORM_FIELD => $form,
            FormChangeContext::SUBMITTED_DATA_FIELD => $submitData,
            FormChangeContext::RESULT_FIELD => []
        ]);

        $this->orderItemFormChangesProvider->addProvider('type1', $this->createProviderMock([
            'type' => 'type1',
            'data' => $provider1Data
        ]));
        $this->orderItemFormChangesProvider->addProvider('type2', $this->createProviderMock([
            'type' => 'type2',
            'data' => $provider2Data
        ]));

        $this->orderItemFormChangesProvider->processFormChanges($this->context);

        static::assertEquals(
            $expectedData,
            $this->context->getResult()
        );
    }

    public function processFormChangesDataProvider()
    {
        $notSellableData = $this->getCompositeData(2);
        $notSellableData[$this->getIdentifier(3)] = ['message' => 'message'];

        return [
            'allProductsValid' => [
                'provider1Data' => $this->getProviderData(3, 'value'),
                'provider2Data' => $this->getProviderData(3, 'code'),
                'formHasItems' => true,
                'submitData' => [
                    OrderItemFormChangesProvider::CHANNEL_FIELD => 1,
                    OrderItemFormChangesProvider::ITEMS_FIELD => [
                        ['product' => 1],
                        ['product' => 2],
                        ['product' => 3],
                    ],
                ],
                'expectedData' => [OrderItemFormChangesProvider::ITEMS_FIELD => $this->getCompositeData(3)],
            ],
            'notAllProductsValid' => [
                'provider1Data' => $this->getProviderData(3, 'value'),
                'provider2Data' => $this->getProviderData(2, 'code'),
                'formHasItems' => true,
                'submitData' => [
                    OrderItemFormChangesProvider::CHANNEL_FIELD => 1,
                    OrderItemFormChangesProvider::ITEMS_FIELD => [
                        ['product' => 1],
                        ['product' => 2],
                        ['product' => 3],
                    ],
                ],
                'expectedData' => [OrderItemFormChangesProvider::ITEMS_FIELD => $notSellableData],
            ],
            'AllProductsNotValid' => [
                'provider1Data' => [],
                'provider2Data' => [],
                'formHasItems' => true,
                'submitData' => [
                    OrderItemFormChangesProvider::CHANNEL_FIELD => 1,
                    OrderItemFormChangesProvider::ITEMS_FIELD => [
                        ['product' => 1],
                        ['product' => 2],
                        ['product' => 3],
                    ],
                ],
                'expectedData' => [OrderItemFormChangesProvider::ITEMS_FIELD => [
                    $this->getIdentifier(1) => ['message' => 'message'],
                    $this->getIdentifier(2) => ['message' => 'message'],
                    $this->getIdentifier(3) => ['message' => 'message']
                ]],
            ],
            'formDoesNotHaveItemsField' => [
                'provider1Data' => $this->getProviderData(3, 'value'),
                'provider2Data' => $this->getProviderData(3, 'code'),
                'formHasItems' => false,
                'submitData' => [
                    OrderItemFormChangesProvider::CHANNEL_FIELD => 1,
                    OrderItemFormChangesProvider::ITEMS_FIELD => [
                        ['product' => 1],
                        ['product' => 2],
                        ['product' => 3],
                    ],
                ],
                'expectedData' => [],
            ],
            'submitDataDoesNotHaveItems' => [
                'provider1Data' => $this->getProviderData(3, 'value'),
                'provider2Data' => $this->getProviderData(3, 'code'),
                'formHasItems' => true,
                'submitData' => [
                    OrderItemFormChangesProvider::CHANNEL_FIELD => 1
                ],
                'expectedData' => [],
            ],
            'submitDataDoesNotHaveSalesChannel' => [
                'provider1Data' => $this->getProviderData(3, 'value'),
                'provider2Data' => $this->getProviderData(3, 'code'),
                'formHasItems' => true,
                'submitData' => [
                    OrderItemFormChangesProvider::ITEMS_FIELD => [
                        ['product' => 1],
                        ['product' => 2],
                        ['product' => 3],
                    ],
                ],
                'expectedData' => [],
            ]
        ];
    }
}
