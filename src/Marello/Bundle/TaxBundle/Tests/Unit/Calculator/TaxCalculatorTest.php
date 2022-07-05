<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Calculator;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\TaxBundle\Calculator\TaxCalculator;
use Marello\Bundle\TaxBundle\Calculator\TaxCalculatorInterface;
use Marello\Bundle\PricingBundle\DependencyInjection\Configuration;

class TaxCalculatorTest extends TestCase
{
    /**
     * @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configManager;

    protected function setUp(): void
    {
        $this->configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @dataProvider taxDataProvider
     *
     * @param bool $taxConfig
     * @param int $inclCalculatorRun
     * @param int $exclCalculatorRun
     */
    public function testTaxIncluded($taxConfig, $inclCalculatorRun, $exclCalculatorRun)
    {
        /** @var TaxCalculatorInterface|\PHPUnit\Framework\MockObject\MockObject $taxIncl */
        $taxIncl = $this->createMock(TaxCalculatorInterface::class);

        /** @var TaxCalculatorInterface|\PHPUnit\Framework\MockObject\MockObject $taxExcl */
        $taxExcl = $this->createMock(TaxCalculatorInterface::class);

        $this->configManager
            ->expects($this->once())
            ->method('get')
            ->with(Configuration::VAT_SYSTEM_CONFIG_PATH)
            ->willReturn($taxConfig);
        $taxIncl->expects($this->exactly($inclCalculatorRun))->method('calculate');
        $taxExcl->expects($this->exactly($exclCalculatorRun))->method('calculate');

        $calculator = new TaxCalculator($this->configManager, $taxIncl, $taxExcl);
        $calculator->calculate(0, 0);
    }

    public function taxDataProvider()
    {
        return [
            'inclTax' => [
                'taxConfig' => true,
                'inclCalculatorRun' => 1,
                'exclCalculatorRun' => 0,
            ],
            'exclTax' => [
                'taxConfig' => false,
                'inclCalculatorRun' => 0,
                'exclCalculatorRun' => 1,
            ]
        ];
    }
}
