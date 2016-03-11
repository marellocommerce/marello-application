<?php

namespace Marello\Bundle\PricingBundle\Tests\Unit\Twig;

use Marello\Bundle\PricingBundle\Twig\PricingExtension;
use Marello\Bundle\PricingBundle\Provider\CurrencyProvider;

class PricingExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $provider;

    /** @var PricingExtension */
    protected $extension;

    protected $data = [
        'currencyCode' => 'USD',
        'currencySymbol' => '$'
    ];

    protected function setUp()
    {
        $this->provider = $this->getMockBuilder(CurrencyProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new PricingExtension($this->provider);
    }

    protected function tearDown()
    {
        unset($this->extension);
        unset($this->provider);
    }

    public function testGetName()
    {
        $this->assertEquals(PricingExtension::NAME, $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(1, $functions);

        $expectedFunctions = array(
            'marello_pricing_get_currency_data'
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
