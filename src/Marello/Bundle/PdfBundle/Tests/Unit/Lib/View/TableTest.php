<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Lib\View;

use Marello\Bundle\PdfBundle\Lib\View\Line;
use Marello\Bundle\PdfBundle\Lib\View\Table;
use PHPUnit\Framework\TestCase;

class TableTest extends TestCase
{
    const MAX_HEIGHT = 13;
    const HEADER_HEIGHT = 5;
    const FOOTER_HEIGHT = 4;

    protected $table;

    public function setUp(): void
    {
        $this->table = new Table(
            self::MAX_HEIGHT,
            self::HEADER_HEIGHT,
            self::FOOTER_HEIGHT
        );
    }

    public function testDisableHeader()
    {
        $this->table->disableHeader();

        $this->assertEquals(self::MAX_HEIGHT - self::FOOTER_HEIGHT, $this->table->getMaxHeight());
    }

    public function testDisableFooter()
    {
        $this->table->disableFooter();

        $this->assertEquals(self::MAX_HEIGHT - self::HEADER_HEIGHT, $this->table->getMaxHeight());
    }

    /**
     * @param $lines
     *
     * @dataProvider addLineProvider
     */
    public function testAddLine($lines)
    {
        $line = new Line(['test-field-1']);
        $line['test-field-1'] = $lines;

        $this->table->addLine($line);

        $tableLines = $this->table->getLines()->filter(function ($x) { return $x->isEmpty() === false; });

        $this->assertCount(1, $tableLines);
        $this->assertEquals($line, $tableLines->first());
    }

    /**
     * @param $lines
     *
     * @dataProvider addLineProvider
     */
    public function testGetHeight($lines)
    {
        $height = $this->table->getHeight();

        $line = new Line(['test-field-1']);
        $line['test-field-1'] = $lines;

        $this->table->addLine($line);

        $this->assertEquals($height + count($lines), $this->table->getHeight());
    }

    public function addLineProvider()
    {
        return [
            'empty' => [
                'lines' => [],
            ],
            'single' => [
                'lines' => ['test'],
            ],
            'multiple' => [
                'lines' => ['test line 1', 'test line 2'],
            ],
        ];
    }

    public function testGetMaxHeight()
    {
        $this->assertEquals(self::MAX_HEIGHT - self::HEADER_HEIGHT - self::FOOTER_HEIGHT, $this->table->getMaxHeight());
        $this->table->disableHeader();
        $this->table->disableFooter();
        $this->assertEquals(self::MAX_HEIGHT, $this->table->getMaxHeight());
    }

    /**
     * @param $tableLines
     * @param $addLine
     * @param $isValid
     *
     * @dataProvider addFitsLineProvider
     */
    public function testFitsLine($tableLines, $addLine, $isValid)
    {
        foreach ($tableLines as $line) {
            $tableLine = new Line(['test-field']);
            $tableLine['test-field'] = $line;

            $this->table->addLine($tableLine);
        }

        $line = new Line(['test-field']);
        $line['test-field'] = $addLine;

        $this->assertEquals($isValid, $this->table->fitsLine($line));
    }

    public function addFitsLineProvider()
    {
        $singleLine = ['test add 1'];
        $multiLine = ['test add 1', 'test add 2'];
        $tooManyLine = ['test add 1', 'test add 2', 'test add 3', 'test add 4', 'test add 5'];

        $emptyTable = [];
        $nonEmptyTable = ['test table 1', 'test table 2'];
        $fullTable = ['test table 1', 'test table 2', 'test table 3', 'test table 4'];

        return [
            'empty add single' => [
                'tableLines' => $emptyTable,
                'addLine' => $singleLine,
                'isValid' => true,
            ],
            'empty add multiple' => [
                'tableLines' => $emptyTable,
                'addLine' => $multiLine,
                'isValid' => true,
            ],
            'empty add too many' => [
                'tableLines' => $emptyTable,
                'addLine' => $tooManyLine,
                'isValid' => false,
            ],
            'nonempty add single' => [
                'tableLines' => $nonEmptyTable,
                'addLine' => $singleLine,
                'isValid' => true,
            ],
            'nonempty add multiple' => [
                'tableLines' => $nonEmptyTable,
                'addLine' => $multiLine,
                'isValid' => true,
            ],
            'nonempty add too many' => [
                'tableLines' => $nonEmptyTable,
                'addLine' => $tooManyLine,
                'isValid' => false,
            ],
            'full add single' => [
                'tableLines' => $fullTable,
                'addLine' => $singleLine,
                'isValid' => false,
            ],
            'full add multiple' => [
                'tableLines' => $fullTable,
                'addLine' => $multiLine,
                'isValid' => false,
            ],
            'full add too many' => [
                'tableLines' => $fullTable,
                'addLine' => $tooManyLine,
                'isValid' => false,
            ],
        ];
    }
}
