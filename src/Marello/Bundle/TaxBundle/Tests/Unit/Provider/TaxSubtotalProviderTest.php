<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Provider;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;
use Marello\Bundle\TaxBundle\Event\TaxEventDispatcher;
use Marello\Bundle\TaxBundle\Factory\TaxFactory;
use Marello\Bundle\TaxBundle\Model\Result;
use Marello\Bundle\TaxBundle\Model\ResultElement;
use Marello\Bundle\TaxBundle\Model\Taxable;
use Marello\Bundle\TaxBundle\Provider\TaxSubtotalProvider;
use Symfony\Component\Translation\TranslatorInterface;

class TaxSubtotalProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TaxSubtotalProvider
     */
    protected $provider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TranslatorInterface
     */
    protected $translator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TaxEventDispatcher
     */
    protected $taxEventDispatcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TaxFactory
     */
    protected $taxFactory;

    protected function setUp()
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->expects($this->any())->method('trans')->willReturnCallback(
            function ($message) {
                return ucfirst($message);
            }
        );

        $this->taxEventDispatcher = $this->getMockBuilder(TaxEventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxFactory = $this->getMockBuilder(TaxFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new TaxSubtotalProvider($this->translator, $this->taxEventDispatcher, $this->taxFactory);
    }

    public function testGetName()
    {
        $this->assertEquals(TaxSubtotalProvider::NAME, $this->provider->getName());
    }

    public function testGetSubtotal()
    {
        $total = $this->createTotalResultElement(150, 'USD');
        $tax   = $this->createTaxResultWithTotal($total);

        $taxable = new Taxable();
        $taxable
            ->setAmount(150)
            ->setCurrency('USD');

        $this->taxFactory->expects($this->once())
            ->method('create')
            ->willReturn($taxable);

        $this->taxEventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function (Taxable $taxable) use ($tax) {
                return $taxable->setResult($tax);
            });

        $subtotal = $this->provider->getSubtotal(new Order());

        $this->assertSubtotal($subtotal, $total);
    }

    public function testIsSupported()
    {
        $this->taxFactory->expects($this->once())->method('supports')->willReturn(true);
        $this->assertTrue($this->provider->isSupported(new \stdClass()));
    }

    /**
     * @param Subtotal $subtotal
     * @param ResultElement $total
     */
    protected function assertSubtotal(Subtotal $subtotal, ResultElement $total)
    {
        $this->assertInstanceOf(Subtotal::class, $subtotal);
        $this->assertEquals(TaxSubtotalProvider::TYPE, $subtotal->getType());
        $this->assertEquals('Marello.tax.subtotals.tax.label', $subtotal->getLabel());
        $this->assertEquals($total->getCurrency(), $subtotal->getCurrency());
        $this->assertEquals($total->getTaxAmount(), $subtotal->getAmount());
        $this->assertEquals(TaxSubtotalProvider::SUBTOTAL_ORDER, $subtotal->getSortOrder());
        $this->assertTrue($subtotal->isVisible());
    }

    /**
     * @param int $amount
     * @param string $currency
     * @return ResultElement
     */
    protected function createTotalResultElement($amount, $currency)
    {
        $total = new ResultElement();
        $total
            ->setCurrency($currency)
            ->offsetSet(ResultElement::TAX_AMOUNT, $amount);

        return $total;
    }

    /**
     * @param ResultElement $total
     * @return Result
     */
    protected function createTaxResultWithTotal(ResultElement $total)
    {
        $tax = new Result();
        $tax->offsetSet(Result::TOTAL, $total);

        return $tax;
    }
}
