<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Lib\View;

use Marello\Bundle\PdfBundle\Lib\View\Line;
use PHPUnit\Framework\TestCase;

class LineTest extends TestCase
{
    protected $line;

    public function setUp(): void
    {
        $this->line = new Line([
            'test-field-1',
            'test-field-2',
        ]);
    }

    public function testOffsetExists()
    {
        $this->line['test-field-1'] = ['test value 1'];

        $this->assertTrue(isset($this->line['test-field-1']));
        $this->assertTrue(isset($this->line['test-field-2']));
        $this->assertFalse(isset($this->line['test-field-3']));
    }

    public function testOffsetGet()
    {
        $this->line['test-field-1'] = ['test value 1'];

        $this->assertEquals(['test value 1'], $this->line['test-field-1']);
        $this->assertEquals([null], $this->line['test-field-2']);

        $this->expectException(\InvalidArgumentException::class);
        $this->line['test-field-3'];
    }

    public function testOffsetSet()
    {
        $this->line['test-field-1'] = ['test value 1'];

        $this->assertEquals(['test value 1'], $this->line['test-field-1']);

        $this->expectException(\InvalidArgumentException::class);
        $this->line['test-field-3'] = ['test value 3'];
    }

    public function testOffsetUnset()
    {
        $this->line['test-field-1'] = ['test value 1'];

        unset($this->line['test-field-1']);

        $this->assertEquals([null], $this->line['test-field-1']);

        $this->expectException(\InvalidArgumentException::class);
        unset($this->line['test-field-3']);
    }

    /**
     * @param $fieldValues
     * @param $returnValue
     *
     * @dataProvider getDisplayLinesProvider
     */
    public function testGetDisplayLines($fieldValues, $returnValue)
    {
        foreach ($fieldValues as $key => $value) {
            $this->line[$key] = $value;
        }

        $displayLines = $this->line->getDisplayLines();
        $this->assertTrue(is_array($displayLines));
        $this->assertCount(count($returnValue), $displayLines);

        foreach ($returnValue as $i => $lineValue) {
            foreach ($lineValue as $key => $fieldValue) {
                $this->assertEquals($fieldValue, $displayLines[$i][$key]);
            }
        }
    }

    /**
     * @return array
     */
    public function getDisplayLinesProvider()
    {
        return [
            'empty' => [
                'fieldValues' => [],
                'returnValue' => [
                    ['test-field-1' => null, 'test-field-2' => null],
                ],
            ],
            'single' => [
                'fieldValues' => [
                    'test-field-1' => ['test value 1'],
                ],
                'returnValue' => [
                    ['test-field-1' => 'test value 1', 'test-field-2' => null],
                ],
            ],
            'multiple' => [
                'fieldValues' => [
                    'test-field-1' => ['test value line 1', 'test value line 2'],
                ],
                'returnValue' => [
                    ['test-field-1' => 'test value line 1', 'test-field-2' => null],
                    ['test-field-1' => 'test value line 2', 'test-field-2' => null],
                ],
            ],
        ];
    }

    /**
     * @param $fieldValues
     * @param $height
     *
     * @dataProvider getHeightProvider
     */
    public function testGetHeight($fieldValues, $height)
    {
        foreach ($fieldValues as $field => $value) {
            $this->line[$field] = $value;
        }

        $this->assertEquals($height, $this->line->getHeight());
    }

    /**
     * @return array
     */
    public function getHeightProvider()
    {
        return [
            'empty' => [
                'fieldValues' => [],
                'height' => 1,
            ],
            'single' => [
                'fieldValues' => [
                    'test-field-1' => ['test line 1'],
                ],
                'height' => 1,
            ],
            'multiple' => [
                'fieldValues' => [
                    'test-field-1' => ['test line 1', 'test line 2'],
                ],
                'height' => 2,
            ],
        ];
    }

    /**
     * @param $fieldValues
     * @param $isEmpty
     *
     * @dataProvider isEmptyProvider
     */
    public function testIsEmpty($fieldValues, $isEmpty)
    {
        foreach ($fieldValues as $field => $value) {
            $this->line[$field] = $value;
        }

        $this->assertEquals($isEmpty, $this->line->isEmpty());
    }

    /**
     * @return array
     */
    public function isEmptyProvider()
    {
        return [
            'empty' => [
                'fieldValues' => [],
                'isEmpty' => true,
            ],
            'not empty' => [
                'fieldValues' => [
                    'test-field-1' => 'Value 1',
                    'test-field-2' => 'Value 2',
                ],
                'isEmpty' => false,
            ],
        ];
    }
}
