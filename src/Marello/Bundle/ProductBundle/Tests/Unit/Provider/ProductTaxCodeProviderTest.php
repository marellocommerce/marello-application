<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\Provider;

use Oro\Bundle\EntityBundle\ORM\Registry;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\Form\FormInterface;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTrait;

use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\LayoutBundle\Context\FormChangeContext;
use Marello\Bundle\ProductBundle\Provider\ProductTaxCodeProvider;
use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;

class ProductTaxCodeProviderTest extends TestCase
{
    use EntityTrait;

    /**
     * @var Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    /**
     * @var AclHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $aclHelper;

    /**
     * @var FormChangeContextInterface
     */
    protected $context;

    /**
     * @var ProductTaxCodeProvider
     */
    protected $productTaxCodeProvider;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(Registry::class);
        $this->aclHelper = $this->createMock(AclHelper::class);
        $this->productTaxCodeProvider = new ProductTaxCodeProvider($this->registry, $this->aclHelper);
    }

    /**
     * @dataProvider getDataDataProvider
     *
     * @param TaxCode|null $channelTaxCode
     * @param TaxCode $defaultTaxCode
     * @param string $expectedValue
     */
    public function testProcessFormChanges($channelTaxCode, TaxCode $defaultTaxCode, $expectedValue)
    {
        /** @var SalesChannel $channel */
        $channel = $this->getEntity(SalesChannel::class, ['id' => 1, 'currency' => 'EUR']);
        /** @var Order $order */
        $order = $this->getEntity(Order::class, ['id' => 1, 'salesChannel' => $channel]);

        /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject $form **/
        $form = $this->createMock(FormInterface::class);
        $form->expects(static::once())
            ->method('getData')
            ->willReturn($order);

        /** @var Product|\PHPUnit\Framework\MockObject\MockObject $product */
        $product = $this->createMock(Product::class);
        $product->expects(static::any())
            ->method('getId')
            ->willReturn(1);
        $product->expects(static::once())
            ->method('getSalesChannelTaxCode')
            ->with($channel)
            ->willReturn($channelTaxCode);
        $product->expects(static::any())
            ->method('getTaxCode')
            ->willReturn($defaultTaxCode);

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository
            ->expects(static::once())
            ->method('findBySalesChannel')
            ->with($channel->getId(), [$product->getId()])
            ->willReturn([$product]);

        $this->registry
            ->expects(static::once())
            ->method('getManagerForClass')
            ->with(Product::class)
            ->willReturnSelf();
        $this->registry
            ->expects(static::once())
            ->method('getRepository')
            ->with(Product::class)
            ->willReturn($productRepository);

        $expectedData = [
            ProductTaxCodeProvider::ITEMS_FIELD => [
                'tax_code' => [
                    sprintf('%s%s', ProductTaxCodeProvider::IDENTIFIER_PREFIX, $product->getId()) => $expectedValue,
                ]
            ]
        ];

        $this->context = new FormChangeContext([
            FormChangeContext::FORM_FIELD => $form,
            FormChangeContext::SUBMITTED_DATA_FIELD => [
                ProductTaxCodeProvider::ITEMS_FIELD => [['product' => $product->getId()]]
            ],
            FormChangeContext::RESULT_FIELD => []
        ]);

        $this->productTaxCodeProvider->processFormChanges($this->context);

        static::assertEquals(
            $expectedData,
            $this->context->getResult()
        );
    }

    public function getDataDataProvider()
    {
        $defaultTaxCode = $this->getEntity(TaxCode::class, ['id' => 1, 'code' => 'TAX_DEFAULT']);

        return [
            'noChannelTaxCode' => [
                'channelTaxCode' => null,
                'defaultTaxCode' => $defaultTaxCode,
                'expectedValue' => ['id' => 1, 'code' => 'TAX_DEFAULT']
            ],
            'withChannelTaxCode' => [
                'channelTaxCode' => $this->getEntity(TaxCode::class, ['id' => 2, 'code' => 'TAX_CHANNEL']),
                'defaultTaxCode' => $defaultTaxCode,
                'expectedValue' => ['id' => 2, 'code' => 'TAX_CHANNEL']
            ]
        ];
    }
}
