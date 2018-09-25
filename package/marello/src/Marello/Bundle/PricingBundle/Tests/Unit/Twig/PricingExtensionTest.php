<?php

namespace Marello\Bundle\PricingBundle\Tests\Unit\Twig;

use Marello\Bundle\PricingBundle\Formatter\LabelVATAwareFormatter;
use Marello\Bundle\PricingBundle\Twig\PricingExtension;
use Marello\Bundle\PricingBundle\Provider\CurrencyProvider;

class PricingExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CurrencyProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyProvider;

    /**
     * @var LabelVATAwareFormatter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $vatLabelFormatter;

    /** @var PricingExtension */
    protected $extension;

    protected $data = [
        'currencyCode' => 'USD',
        'currencySymbol' => '$'
    ];

    protected function setUp()
    {
        $this->currencyProvider = $this->getMockBuilder(CurrencyProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->vatLabelFormatter = $this->getMockBuilder(LabelVATAwareFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new PricingExtension($this->currencyProvider, $this->vatLabelFormatter);
    }

    protected function tearDown()
    {
        unset($this->extension);
        unset($this->currencyProvider);
        unset($this->vatLabelFormatter);
    }

    public function testGetName()
    {
        $this->assertEquals(PricingExtension::NAME, $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(2, $functions);

        $expectedFunctions = array(
            'marello_pricing_get_currency_data',
            'marello_pricing_vat_aware_label'
        );

        /** @var \Twig_SimpleFunction $function */
        foreach ($functions as $function) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $function);
            $this->assertContains($function->getName(), $expectedFunctions);
        }
    }

    public function testGetCurrencyData()
    {
        $expectedFormat = sprintf('%s (%s)', $this->data['currencyCode'], $this->data['currencySymbol']);
        $this->assertEquals($expectedFormat, $this->extension->getCurrencyData($this->data));
    }
}
