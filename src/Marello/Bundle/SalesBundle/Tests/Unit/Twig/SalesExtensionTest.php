<?php

namespace Marello\Bundle\SalesBundle\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Twig\SalesExtension;
use Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository;
use Twig\TwigFunction;

class SalesExtensionTest extends TestCase
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
                new TwigFunction(
                    'marello_sales_has_active_channels',
                    [$this->extension, 'checkActiveChannels']
                ),
                new TwigFunction(
                    'marello_get_sales_channel_name_by_code',
                    [$this->extension, 'getChannelNameByCode']
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

    /**
     * @param $expectedChannel
     * @param $code
     * @param $expectedResult
     * @dataProvider getChannelNameByCodeDataProvider
     */
    public function testGetChannelNameByCode($expectedChannel, $code, $expectedResult)
    {
        $this->salesChannelRepository
            ->expects(static::once())
            ->method('findOneBy')
            ->willReturn($expectedChannel);

        static::assertEquals($expectedResult, $this->extension->getChannelNameByCode($code));
    }

    /**
     * @return array[]
     */
    public function getChannelNameByCodeDataProvider()
    {
        $channel = new SalesChannel('channel1');
        $channel->setCode('test');
        return [
            'with active channels' => [
                'expectedChannel' => $channel,
                'code' => 'test',
                'expectedResult' => 'channel1',
            ],
            'without active channels' => [
                'expectedChannel' => null,
                'code' => 'MySalesChannelCode',
                'expectedResult' => 'MySalesChannelCode'
            ],
        ];
    }
}
