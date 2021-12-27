<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Provider;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;
use Marello\Bundle\PricingBundle\DependencyInjection\Configuration;
use Marello\Bundle\TaxBundle\Provider\TaxSubtotalOperationProvider;

class TaxSubtotalOperationProviderTest extends TestCase
{
    /**
     * @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configManager;

    /**
     * @var TaxSubtotalOperationProvider
     */
    protected $taxSubtotalOperationProvider;

    protected function setUp(): void
    {
        $this->configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxSubtotalOperationProvider = new TaxSubtotalOperationProvider($this->configManager);
    }

    /**
     * @dataProvider subtotalOperationDataProvider
     *
     * @param bool $config
     * @param int $operation
     */
    public function testGetSubtotalOperation($config, $operation)
    {
        $this->configManager
            ->expects(static::once())
            ->method('get')
            ->with(Configuration::VAT_SYSTEM_CONFIG_PATH)
            ->willReturn($config);

        static::assertEquals($operation, $this->taxSubtotalOperationProvider->getSubtotalOperation());
    }

    public function subtotalOperationDataProvider()
    {
        return [
            'included' => [
                'config' => true,
                'operation' =>Subtotal::OPERATION_SUBTRACTION
            ],
            'excluded' => [
                'config' => false,
                'operation' =>Subtotal::OPERATION_IGNORE
            ]
        ];
    }
}
