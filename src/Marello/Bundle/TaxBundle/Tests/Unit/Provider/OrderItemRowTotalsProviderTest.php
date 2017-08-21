<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Provider;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\LayoutBundle\Context\FormChangeContext;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\TaxBundle\Calculator\TaxCalculatorInterface;
use Marello\Bundle\TaxBundle\Entity\TaxRate;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Marello\Bundle\TaxBundle\Matcher\TaxRuleMatcherInterface;
use Marello\Bundle\TaxBundle\Model\ResultElement;
use Marello\Bundle\TaxBundle\Provider\OrderItemRowTotalsProvider;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Form\FormInterface;

class OrderItemRowTotalsProviderTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var TaxCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxCalculator;

    /**
     * @var TaxRuleMatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxRuleMatcher;

    /**
     * @var OrderItemRowTotalsProvider
     */
    protected $orderItemRowTotalsProvider;

    protected function setUp()
    {
        $this->taxCalculator = $this->createMock(TaxCalculatorInterface::class);
        $this->taxRuleMatcher = $this->createMock(TaxRuleMatcherInterface::class);
        $this->orderItemRowTotalsProvider = new OrderItemRowTotalsProvider(
            $this->taxCalculator,
            $this->taxRuleMatcher
        );
    }

    /**
     * @dataProvider processFormChangesDataProvider
     *
     * @param TaxRule|null $matchedRule
     * @param array $submittedData
     * @param array $resultBefore
     * @param array $calculationResult
     * @param array $expectedResult
     */
    public function testProcessFormChanges(
        TaxRule $matchedRule = null,
        array $submittedData,
        array $resultBefore,
        array $calculationResult,
        array $expectedResult
    ) {
        /** @var MarelloAddress|\PHPUnit_Framework_MockObject_MockObject $shippingAddress **/
        $shippingAddress = $this->createMock(MarelloAddress::class);
        /** @var Order $order */
        $order = $this->getEntity(Order::class, ['id' => 1, 'shippingAddress' => $shippingAddress]);

        /** @var FormInterface|\PHPUnit_Framework_MockObject_MockObject $form **/
        $form = $this->createMock(FormInterface::class);
        $form->expects(static::once())
            ->method('getData')
            ->willReturn($order);
        
        $this->taxRuleMatcher
            ->expects(static::any())
            ->method('match')
            ->with($order->getShippingAddress(), ['TEST_CODE'])
            ->willReturn($matchedRule);
        
        $this->taxCalculator
            ->expects(static::any())
            ->method('calculate')
            ->willReturn(new ResultElement($calculationResult));

        $context = new FormChangeContext([
            FormChangeContext::FORM_FIELD => $form,
            FormChangeContext::SUBMITTED_DATA_FIELD => $submittedData,
            FormChangeContext::RESULT_FIELD => $resultBefore
        ]);
        $this->orderItemRowTotalsProvider->processFormChanges($context);

        static::assertEquals($expectedResult, $context->getResult());
    }

    public function processFormChangesDataProvider()
    {
        /** @var TaxRate $taxRate */
        $taxRate = $this->getEntity(TaxRate::class, ['id' => 1, 'rate' => 0.1]);
        /** @var TaxRule $matchedRule */
        $matchedRule = $this->getEntity(TaxRule::class, ['id' => 1, 'taxRate' => $taxRate]);

        $resultBefore = [
            OrderItemRowTotalsProvider::ITEMS_FIELD => [
                'price' => [
                    'product-id-1' => ['value' => 50]
                ],
                'tax_code' => [
                    'product-id-1' => ['code' => 'TEST_CODE']
                ]
            ]
        ];

        $calculationResult = [
            ResultElement::INCLUDING_TAX => 100,
            ResultElement::EXCLUDING_TAX => 90,
            ResultElement::TAX_AMOUNT => 10
        ];

        $expectedResults = $resultBefore;
        $expectedResults[OrderItemRowTotalsProvider::ITEMS_FIELD]['row_totals']['product-id-1'] = $calculationResult;

        return [
            [
                'matchedRule' => $matchedRule,
                'submittedData' => [
                    OrderItemRowTotalsProvider::ITEMS_FIELD => [
                        ['product' => 1, 'quantity' => 2],
                    ]
                ],
                'resultBefore' => $resultBefore,
                'calculationResult' => $calculationResult,
                'expectedResult' => $expectedResults
            ],
            [
                'matchedRule' => $matchedRule,
                'submittedData' => [
                    OrderItemRowTotalsProvider::ITEMS_FIELD => []
                ],
                'resultBefore' => $resultBefore,
                'calculationResult' => $calculationResult,
                'expectedResult' => $resultBefore
            ],
            [
                'matchedRule' => null,
                'submittedData' => [
                    OrderItemRowTotalsProvider::ITEMS_FIELD => [
                        ['product' => 1, 'quantity' => 2],
                    ]
                ],
                'resultBefore' => $resultBefore,
                'calculationResult' => $calculationResult,
                'expectedResult' => $expectedResults
            ],
        ];
    }
}
