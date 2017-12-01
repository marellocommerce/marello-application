<?php

namespace Marello\Bundle\PricingBundle\Tests\Unit\Formatter;

use Marello\Bundle\PricingBundle\DependencyInjection\Configuration;
use Marello\Bundle\PricingBundle\Formatter\LabelVATAwareFormatter;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\Translation\TranslatorInterface;

class LabelVATAwareFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configManager;

    /**
     * @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translator;

    /**
     * @var LabelVATAwareFormatter
     */
    protected $labelVATAwareFormatter;

    protected function setUp()
    {
        $this->configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->labelVATAwareFormatter = new LabelVATAwareFormatter($this->configManager, $this->translator);
    }

    /**
     * @dataProvider formattedLabelDataProvider
     *
     * @param string $labelBeforeTranslation
     * @param string $labelAfterTranslation
     * @param string $suffixTranslation
     * @param string $suffixValue
     * @param bool $confValue
     * @param string $expectedValue
     */
    public function testGetFormattedLabel(
        $labelBeforeTranslation,
        $labelAfterTranslation,
        $suffixTranslation,
        $suffixValue,
        $confValue,
        $expectedValue
    ) {
        $this->translator
            ->expects(static::exactly(2))
            ->method('trans')
            ->willReturnMap(
                [
                    [$suffixTranslation, [], null, null, $suffixValue],
                    [$labelBeforeTranslation, [], null, null,  $labelAfterTranslation],
                ]
            );
        $this->configManager
            ->expects(static::once())
            ->method('get')
            ->with(Configuration::VAT_SYSTEM_CONFIG_PATH)
            ->willReturn($confValue);

        static::assertEquals($expectedValue, $this->labelVATAwareFormatter->getFormattedLabel($labelBeforeTranslation));
    }

    /**
     * @return array
     */
    public function formattedLabelDataProvider()
    {
        return [
            'incl_VAT' => [
                'labelBeforeTranslation' => 'marello_pricing.label',
                'labelAfterTranslation' => 'Price',
                'suffixTranslation' => LabelVATAwareFormatter::TRANSLATION_INCL_VAT,
                'suffixValue' => 'incl. VAT',
                'confValue' => true,
                'expectedValue' => 'Price incl. VAT'
            ],
            'excl_VAT' => [
                'labelBeforeTranslation' => 'marello_amount.label',
                'labelAfterTranslation' => 'Amount',
                'suffixTranslation' => LabelVATAwareFormatter::TRANSLATION_EXCL_VAT,
                'suffixValue' => 'excl. VAT',
                'confValue' => false,
                'expectedValue' => 'Amount excl. VAT'
            ],
        ];
    }
}
