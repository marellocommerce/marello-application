<?php

namespace Marello\Bundle\PricingBundle\Tests\Unit\EventListener\Datagrid;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

use Marello\Bundle\PricingBundle\Formatter\LabelVATAwareFormatter;
use Marello\Bundle\PricingBundle\EventListener\Datagrid\PriceVATLabelAwareGridListener;

class PriceVATLabelAwareGridListenerTest extends TestCase
{
    const ORIGINAL_LABEL = 'Original Label';
    const FORMATTED_LABEL = 'Formatted Label';

    /**
     * @var LabelVATAwareFormatter|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $vatLabelFormatter;

    /**
     * @var PriceVATLabelAwareGridListener
     */
    protected $listener;

    protected function setUp(): void
    {
        $this->vatLabelFormatter = $this->getMockBuilder(LabelVATAwareFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->listener = new PriceVATLabelAwareGridListener($this->vatLabelFormatter);
    }

    /**
     * @dataProvider onBuildBeforeDataProvider
     *
     * @param string $column
     * @param string $expectedLabel
     */
    public function testOnBuildBefore($column, $expectedLabel)
    {
        /** @var DatagridInterface|\PHPUnit\Framework\MockObject\MockObject $dataGrid */
        $dataGrid = $this->createMock(DatagridInterface::class);

        $gridConfig = DatagridConfiguration::create(['name' => 'marello-order-items']);
        $gridConfig->offsetSetByPath(sprintf('[columns][%s]', $column), ['label' => self::ORIGINAL_LABEL]);

        $this->vatLabelFormatter
            ->expects(static::any())
            ->method('getFormattedLabel')
            ->with(self::ORIGINAL_LABEL)
            ->willReturn(self::FORMATTED_LABEL);

        $event = new BuildBefore($dataGrid, $gridConfig);

        $this->listener->onBuildBefore($event);

        static::assertEquals(
            [
                'name' => 'marello-order-items',
                'columns' => [
                    $column => [
                        'label' => $expectedLabel
                    ]
                ]
            ],
            $gridConfig->toArray()
        );
    }

    public function onBuildBeforeDataProvider()
    {
        return [
            'withPriceColumn' => [
                'column' => 'price',
                'expectedLabel' => self::FORMATTED_LABEL
            ],
            'withoutPriceColumn' => [
                'column' => 'quantity',
                'expectedLabel' => self::ORIGINAL_LABEL
            ]
        ];
    }
}
