<?php

namespace Marello\Bundle\SalesBundle\Tests\Unit\Twig;

use Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Twig\SalesExtension;

class SalesExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SalesChannelRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesChannelRepository;

    /**
     * @var SalesExtension
     */
    protected $extension;

    public function setUp()
    {
        $this->salesChannelRepository = $this->createMock(SalesChannelRepository::class);

        $this->extension = new SalesExtension($this->salesChannelRepository);
    }

    public function testGetFunctions()
    {
        static::assertEquals(
            [
                new \Twig_SimpleFunction(
                    'marello_sales_has_active_channels',
                    [$this->extension, 'checkActiveChannels']
                )
            ],
            $this->extension->getFunctions()
        );
    }

    public function testGetName()
    {
        static::assertEquals(SalesExtension::NAME, $this->extension->getName());
    }

    /**
     * @param array $expectedChannels
     * @param boolean $expectedResult
     *
     * @dataProvider checkActiveChannelsDataProvider
     */
    public function testCheckActiveChannels(array $expectedChannels, $expectedResult)
    {
        $this->salesChannelRepository
           ->expects(static::once())
           ->method('getActiveChannels')
           ->willReturn($expectedChannels);

        static::assertEquals($expectedResult, $this->extension->checkActiveChannels());
    }

    /**
     * @return array
     */
    public function checkActiveChannelsDataProvider()
    {
        return [
            'with active channels' => [
                'expectedChannels' => [
                    new SalesChannel('channel1'),
                    new SalesChannel('channel2'),
                ],
                'expectedResult' => true,
            ],
            'without active channels' => [
                'expectedChannels' => [],
                'expectedResult' => false,
            ],
        ];
    }
}
