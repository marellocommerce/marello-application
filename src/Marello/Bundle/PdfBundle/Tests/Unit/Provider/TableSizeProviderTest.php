<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Provider;

use Marello\Bundle\PdfBundle\DependencyInjection\Configuration;
use Marello\Bundle\PdfBundle\Exception\PaperSizeNotSetException;
use Marello\Bundle\PdfBundle\Provider\TableSizeProvider;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class TableSizeProviderTest extends TestCase
{
    use EntityTrait;

    /**
     * @var SalesChannel
     */
    protected $salesChannel;

    public function setUp(): void
    {
        $this->salesChannel = $this->getEntity(SalesChannel::class, ['id' => 1, 'name' => 'Test Sales Channel', 'code' => 'test1']);
    }

    /**
     * @param $maxHeight
     * @param $maxTextWidth
     * @param $firstPageInfoHeight
     * @param $lastPageInfoHeight
     * @param $pageSize
     *
     * @dataProvider dataProvider
     */
    public function testGetMaxHeight($maxHeight, $maxTextWidth, $firstPageInfoHeight, $lastPageInfoHeight, $pageSize)
    {
        $provider = $this->getProvider($maxHeight, $maxTextWidth, $firstPageInfoHeight, $lastPageInfoHeight, $pageSize);

        $result = $this->getPageSizeProviderResult($maxHeight, $pageSize);

        $this->assertEquals($result, $provider->getMaxHeight($this->salesChannel));
    }

    /**
     * @param $maxHeight
     * @param $maxTextWidth
     * @param $firstPageInfoHeight
     * @param $lastPageInfoHeight
     * @param $pageSize
     *
     * @dataProvider dataProvider
     */
    public function testGetMaxTextWidth($maxHeight, $maxTextWidth, $firstPageInfoHeight, $lastPageInfoHeight, $pageSize)
    {
        $provider = $this->getProvider($maxHeight, $maxTextWidth, $firstPageInfoHeight, $lastPageInfoHeight, $pageSize);

        $result = $this->getPageSizeProviderResult($maxTextWidth, $pageSize);

        $this->assertEquals($result, $provider->getMaxTextWidth($this->salesChannel));
    }

    /**
     * @param $maxHeight
     * @param $maxTextWidth
     * @param $firstPageInfoHeight
     * @param $lastPageInfoHeight
     * @param $pageSize
     *
     * @dataProvider dataProvider
     */
    public function testGetFirstPageInfoHeight($maxHeight, $maxTextWidth, $firstPageInfoHeight, $lastPageInfoHeight, $pageSize)
    {
        $provider = $this->getProvider($maxHeight, $maxTextWidth, $firstPageInfoHeight, $lastPageInfoHeight, $pageSize);

        $result = $this->getPageSizeProviderResult($firstPageInfoHeight, $pageSize);

        $this->assertEquals($result, $provider->getFirstPageInfoHeight($this->salesChannel));
    }

    /**
     * @param $maxHeight
     * @param $maxTextWidth
     * @param $firstPageInfoHeight
     * @param $lastPageInfoHeight
     * @param $pageSize
     *
     * @dataProvider dataProvider
     */
    public function testGetLastPageInfoHeight($maxHeight, $maxTextWidth, $firstPageInfoHeight, $lastPageInfoHeight, $pageSize)
    {
        $provider = $this->getProvider($maxHeight, $maxTextWidth, $firstPageInfoHeight, $lastPageInfoHeight, $pageSize);

        $result = $this->getPageSizeProviderResult($lastPageInfoHeight, $pageSize);

        $this->assertEquals($result, $provider->getLastPageInfoHeight($this->salesChannel));
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'a4' => [
                'maxHeight' => ['a4' => 10, 'letter' => 11],
                'maxTextWidth' => ['a4' => 12, 'letter' => 13],
                'firstPageInfoHeight' => ['a4' => 14, 'letter' => 15],
                'lastPageInfoHeight' => ['a4' => 16, 'letter' => 16],
                'pageSize' => Configuration::PAPER_SIZE_A4,
            ],
            'letter' => [
                'maxHeight' => ['a4' => 20, 'letter' => 21],
                'maxTextWidth' => ['a4' => 22, 'letter' => 23],
                'firstPageInfoHeight' => ['a4' => 14, 'letter' => 15],
                'lastPageInfoHeight' => ['a4' => 16, 'letter' => 17],
                'pageSize' => Configuration::PAPER_SIZE_LETTER,
            ],
            'not compound' => [
                'maxHeight' => 30,
                'maxTextWidth' => 32,
                'firstPageInfoHeight' => 34,
                'lastPageInfoHeight' => 36,
                'pageSize' => null,
            ],
            'not set' => [
                'maxHeight' => ['a4' => 20, 'letter' => 21],
                'maxTextWidth' => ['a4' => 22, 'letter' => 23],
                'firstPageInfoHeight' => ['a4' => 14, 'letter' => 15],
                'lastPageInfoHeight' => ['a4' => 16, 'letter' => 17],
                'pageSize' => 'not existing',
            ],
        ];
    }

    /**
     * @param $maxHeight
     * @param $maxTextWidth
     * @param $firstPageInfoHeight
     * @param $lastPageInfoHeight
     * @param $pageSize
     * @return TableSizeProvider
     */
    protected function getProvider($maxHeight, $maxTextWidth, $firstPageInfoHeight, $lastPageInfoHeight, $pageSize)
    {
        /** @var SalesChannel $salesChannel */
        $salesChannel = $this->getEntity(SalesChannel::class, ['id' => 1, 'name' => 'Test Sales Channel', 'code' => 'test1']);

        /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager */
        $configManager = $this->createMock(ConfigManager::class);
        if ($pageSize !== null) {
            $configManager->expects($this->once())
                ->method('get')
                ->with('marello_pdf.paper_size', false, false, $salesChannel)
                ->willReturn($pageSize)
            ;
        }

        return new TableSizeProvider(
            $configManager,
            $maxHeight,
            $maxTextWidth,
            $firstPageInfoHeight,
            $lastPageInfoHeight
        );
    }

    /**
     * @param $values
     * @param $pageSize
     * @return mixed|null
     */
    protected function getPageSizeProviderResult($values, $pageSize)
    {
        $result = null;
        if (!is_array($values)) {
            $result = $values;
        } elseif (isset($values[$pageSize])) {
            $result = $values[$pageSize];
        } else {
            $this->expectException(PaperSizeNotSetException::class);
        }

        return $result;
    }
}
