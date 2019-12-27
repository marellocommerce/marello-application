<?php

namespace Marello\Bundle\PdfBundle\Provider;

use Marello\Bundle\PdfBundle\DependencyInjection\Configuration;
use Marello\Bundle\PdfBundle\Exception\PaperSizeNotSetException;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class TableSizeProvider
{
    const KEY_MAX_HEIGHT = 'max_height';
    const KEY_MAX_TEXT_WIDTH = 'max_text_width';
    const KEY_FIRST_PAGE_INFO = 'first_page_info';
    const KEY_LAST_PAGE_INFO = 'last_page_info';

    protected $config;

    protected $sizeConfig = [];

    private $paperSizes = [];

    public function __construct(
        ConfigManager $config,
        $maxHeight,
        $maxTextWidth,
        $firstPageInfoHeight,
        $lastPageInfoHeight
    ) {
        $this->config = $config;
        $this->sizeConfig = [
            self::KEY_MAX_HEIGHT => $maxHeight,
            self::KEY_MAX_TEXT_WIDTH => $maxTextWidth,
            self::KEY_FIRST_PAGE_INFO => $firstPageInfoHeight,
            self::KEY_LAST_PAGE_INFO => $lastPageInfoHeight,
        ];
    }

    public function getMaxHeight(SalesChannel $salesChannel)
    {
        return $this->getValue($salesChannel, self::KEY_MAX_HEIGHT);
    }

    public function getMaxTextWidth(SalesChannel $salesChannel)
    {
        return $this->getValue($salesChannel, self::KEY_MAX_TEXT_WIDTH);
    }

    public function getFirstPageInfoHeight(SalesChannel $salesChannel)
    {
        return $this->getValue($salesChannel, self::KEY_FIRST_PAGE_INFO);
    }

    public function getLastPageInfoHeight(SalesChannel $salesChannel)
    {
        return $this->getValue($salesChannel, self::KEY_LAST_PAGE_INFO);
    }

    protected function getValue(SalesChannel $salesChannel, $key)
    {
        $compoundValue = $this->sizeConfig[$key];
        if (!is_array($compoundValue)) {
            return $compoundValue;
        }

        if (!isset($compoundValue[$this->getPaperSize($salesChannel)])) {
            throw new PaperSizeNotSetException($this->getPaperSize($salesChannel), $key);
        }

        return $compoundValue[$this->getPaperSize($salesChannel)];
    }

    private function getPaperSize(SalesChannel $salesChannel)
    {
        if (!isset($this->paperSizes[$salesChannel->getId()])) {
            $this->paperSizes[$salesChannel->getId()] = $this->fetchPaperSize($salesChannel);
        }

        return $this->paperSizes[$salesChannel->getId()];
    }

    private function fetchPaperSize(SalesChannel $salesChannel)
    {
        return $this->getConfigValue(Configuration::CONFIG_KEY_PAPER_SIZE, $salesChannel);
    }

    private function getConfigValue($configKey, SalesChannel $salesChannel)
    {
        $key = sprintf('%s.%s', Configuration::CONFIG_NAME, $configKey);

        return $this->config->get($key, false, false, $salesChannel);
    }
}
