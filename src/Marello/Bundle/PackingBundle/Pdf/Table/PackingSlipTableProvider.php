<?php

namespace Marello\Bundle\PackingBundle\Pdf\Table;

use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Marello\Bundle\PdfBundle\Lib\View\Line;
use Marello\Bundle\PdfBundle\Lib\View\Table;
use Marello\Bundle\PdfBundle\Provider\TableProviderInterface;
use Marello\Bundle\PdfBundle\Provider\TableSizeProvider;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class PackingSlipTableProvider implements TableProviderInterface
{
    /**
     * @var bool
     */
    protected $firstPage = true;

    public function __construct(
        protected TableSizeProvider $tableSizeProvider
    ) {}

    public function supports($entity)
    {
        return $entity instanceof PackingSlip;
    }

    public function getTables($entity)
    {
        /** @var PackingSlip $entity */
        $salesChannel = $entity->getSalesChannel();
        $table = $this->createTable($salesChannel);
        $tables = [$table];
        $items = $entity->getItems();
        $count = $items->count();

        $i = 0;
        foreach ($items as $item) {
            $i++;
            $line = $this->createLine($item);

            if ($table->fitsLine($line) === false) {
                $table->disableFooter();

                if ($table->fitsLine($line) === false
                    || $i === $count
                ) {
                    $table = $this->createTable($salesChannel);
                    $tables[] = $table;
                }
            }

            $table->addLine($line);
        }

        return $tables;
    }

    protected function createTable(SalesChannel $salesChannel): Table
    {
        $table = new Table(
            $this->tableSizeProvider->getMaxHeight($salesChannel),
            $this->tableSizeProvider->getFirstPageInfoHeight($salesChannel),
            $this->tableSizeProvider->getLastPageInfoHeight($salesChannel)
        );

        if ($this->firstPage) {
            $this->firstPage = false;
        } else {
            $table->disableHeader();
        }

        return $table;
    }

    protected function createLine(PackingSlipItem $invoiceItem): Line
    {
        $line = $this->createLineObject();

        $line['productSku'] = $invoiceItem->getProductSku();
        $line['productName'] = $invoiceItem->getProductName();
        $line['quantity'] = $invoiceItem->getQuantity();
        $line['productUnit'] = $invoiceItem->getProductUnit();

        return $line;
    }

    protected function createLineObject(): Line
    {
        return new Line([
            'productSku',
            'productName',
            'quantity',
            'productUnit',
        ]);
    }
}
