<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Provider;

use Marello\Bundle\PdfBundle\Provider\RenderParameterProviderInterface;
use Marello\Bundle\PdfBundle\Provider\RenderParametersProvider;
use PHPUnit\Framework\TestCase;

class RenderParametersProviderTest extends TestCase
{
    /**
     * @param $firstProviderSupported
     * @param $secondProviderSupported
     * @param $result
     *
     * @dataProvider getParamsProvider
     */
    public function testGetParams($firstProviderSupported, $secondProviderSupported, $result)
    {
        /** @var RenderParameterProviderInterface|\PHPUnit\Framework\MockObject\MockObject $firstProvider */
        $firstProvider = $this->createMock(RenderParameterProviderInterface::class);
        $firstProvider->expects($this->once())
            ->method('supports')
            ->willReturn($firstProviderSupported)
        ;
        if ($firstProviderSupported) {
            $firstProvider->expects($this->once())
                ->method('getParams')
                ->willReturn(['first' => 'first value'])
            ;
        }

        /** @var RenderParameterProviderInterface|\PHPUnit\Framework\MockObject\MockObject $secondProvider */
        $secondProvider = $this->createMock(RenderParameterProviderInterface::class);
        $secondProvider->expects($this->once())
            ->method('supports')
            ->willReturn($secondProviderSupported)
        ;
        if ($secondProviderSupported) {
            $secondProvider->expects($this->once())
                ->method('getParams')
                ->willReturn(['second' => 'second value'])
            ;
        }

        $provider = new RenderParametersProvider();
        $provider->addProvider($firstProvider);
        $provider->addProvider($secondProvider);

        $entity = new \stdClass();
        $this->assertEquals($result, $provider->getParams($entity, []));
    }

    public function getParamsProvider()
    {
        return [
            'none supported' => [
                'firstProviderSupported' => false,
                'secondProviderSupported' => false,
                'result' => [],
            ],
            'one supported' => [
                'firstProviderSupported' => true,
                'secondProviderSupported' => false,
                'result' => ['first' => 'first value'],
            ],
            'multiple supported' => [
                'firstProviderSupported' => true,
                'secondProviderSupported' => true,
                'result' => ['first' => 'first value', 'second' => 'second value'],
            ],
        ];
    }
}
