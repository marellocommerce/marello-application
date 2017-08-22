<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Provider;

use Marello\Bundle\PricingBundle\DependencyInjection\Configuration;
use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;
use Marello\Bundle\TaxBundle\Provider\TaxSubtotalOperationProvider;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class TaxSubtotalOperationProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configManager;

    /**
     * @var TaxSubtotalOperationProvider
     */
    protected $taxSubtotalOperationProvider;

    protected function setUp()
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
