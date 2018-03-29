<?php

namespace Marello\Bundle\CatalogBundle\Tests\Unit\Formatter;

use Marello\Bundle\CatalogBundle\Formatter\CategoryCodeFormatter;

class CategoryCodeFormatterTest extends \PHPUnit_Framework_TestCase
{
    /** @var CategoryCodeFormatter $formatter */
    protected $formatter;

    protected function setUp()
    {
        $this->formatter = new CategoryCodeFormatter();
    }

    /**
     * Test different input values on formatter
     * @dataProvider getTestInputValues
     * @param $inputValue string
     * @param $expectedValue string
     */
    public function testInputValues($inputValue, $expectedValue)
    {
        $this->assertEquals($expectedValue, $this->formatter->format($inputValue));
    }

    /**
     * Data provider for testing
     * @return array
     */
    public function getTestInputValues()
    {
        return [
            'categoryCodeWithQuotes' => [
                'inputValue' => "this has multiple 'quotes' in the category code",
                'expectedValue' => 'this_has_multiple_quotes_in_the_category_code'
            ],
            'categoryCodeWithQuestionMarks' => [
                'inputValue' => 'what do you expect?',
                'expectedValue' => 'what_do_you_expect'
            ],
            'categoryCodeWithWhitespaces' => [
                'inputValue' => 'has white spaces',
                'expectedValue' => 'has_white_spaces'
            ],
            'categoryCodeWithPercent' => [
                'inputValue' => 'can we break it with a %?',
                'expectedValue' => 'can_we_break_it_with_a'
            ],
        ];
    }
}
