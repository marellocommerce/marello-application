<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Cache\Lifetime\UPSSettings;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Cache\Lifetime\UPSSettings\LifetimeByInvalidateCacheAtFieldProvider;

class LifetimeByInvalidateCacheAtFieldProviderTest extends TestCase
{
    /**
     * @internal
     */
    const PROCESSING_TIME_ERROR_VALUE = 3;

    /**
     * @internal
     */
    const LIFETIME = 86400;

    /**
     * @var LifetimeByInvalidateCacheAtFieldProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new LifetimeByInvalidateCacheAtFieldProvider();
    }

    /**
     * @dataProvider savePriceDataProvider
     *
     * @param string $invalidateCacheAtModifier
     * @param int    $expectedLifetime
     */
    public function testGetLifetime(string $invalidateCacheAtModifier, int $expectedLifetime)
    {
        $settings = $this->createSettingsMock();

        $datetime = new \DateTime($invalidateCacheAtModifier);

        $settings->method('getUpsInvalidateCacheAt')
            ->willReturn($datetime);

        $actualLifetime = $this->provider->getLifetime($settings, self::LIFETIME);

        static::assertLessThan(self::PROCESSING_TIME_ERROR_VALUE, abs($expectedLifetime - $actualLifetime));
    }

    /**
     * @return array
     */
    public function savePriceDataProvider()
    {
        return [
            'earlier than lifetime' => [
                'invalidateCacheAt' => '+3second',
                'expectedLifetime' => 3,
            ],
            'in past' => [
                'invalidateCacheAt' => '-1second',
                'expectedLifetime' => self::LIFETIME,
            ],
            'later than lifetime' => [
                'invalidateCacheAt' => '+24hour+10second',
                'expectedLifetime' => self::LIFETIME,
            ],
        ];
    }

    public function testGenerateLifetimeAwareKey()
    {
        $settings = $this->createSettingsMock();

        $datetime = $this->createMock(\DateTime::class);

        $datetime->method('getTimestamp')
            ->willReturn(1000);

        $settings->method('getUpsInvalidateCacheAt')
            ->willReturn($datetime);

        $key = 'cache_key';

        $expectedKey = 'cache_key_1000';

        static::assertEquals($expectedKey, $this->provider->generateLifetimeAwareKey($settings, $key));
    }

    public function testGenerateLifetimeAwareKeyNull()
    {
        $settings = $this->createSettingsMock();

        $key = 'cache_key';

        $expectedKey = 'cache_key_';

        static::assertEquals($expectedKey, $this->provider->generateLifetimeAwareKey($settings, $key));
    }

    /**
     * @return UPSSettings|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createSettingsMock()
    {
        return $this->createMock(UPSSettings::class);
    }
}
