<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Provider\Render;

use Marello\Bundle\PdfBundle\Provider\Render\ConfigValuesProvider;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use PHPUnit\Framework\TestCase;

class ConfigValuesProviderTest extends TestCase
{
    public function testSupports()
    {
        /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager */
        $configManager = $this->createMock(ConfigManager::class);
        $parameters = [];

        $provider = new ConfigValuesProvider($configManager, $parameters);

        $this->assertTrue($provider->supports('test value', []));
    }

    public function testGetParams()
    {
        $parameters = [
            'parameter-1' => 'value 1',
            'parameter-2' => 'value 2',
        ];

        /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager */
        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->exactly(count($parameters)))
            ->method('get')
            ->withConsecutive(...array_map(function ($x) { return [$x, false, false, null]; }, array_keys($parameters)))
            ->willReturnOnConsecutiveCalls(...array_values($parameters))
        ;

        $providerParameters = array_combine(array_keys($parameters), array_keys($parameters));

        $provider = new ConfigValuesProvider($configManager, $providerParameters);

        $result = $provider->getParams('test value', []);

        $this->assertCount(count($parameters), $result);
        foreach ($parameters as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }
    }

    public function testGetParamsWithScope()
    {
        $parameters = [
            'parameter-1' => 'value 1',
            'parameter-2' => 'value 2',
        ];

        /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager */
        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->exactly(count($parameters)))
            ->method('get')
            ->withConsecutive(...array_map(function ($x) { return [$x, false, false, 'scope']; }, array_keys($parameters)))
            ->willReturnOnConsecutiveCalls(...array_values($parameters))
        ;

        $providerParameters = array_combine(array_keys($parameters), array_keys($parameters));

        $provider = new ConfigValuesProvider($configManager, $providerParameters);

        $result = $provider->getParams('test value', [ConfigValuesProvider::SCOPE_IDENTIFIER_KEY => 'scope']);

        $this->assertCount(count($parameters), $result);
        foreach ($parameters as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }
    }
}
