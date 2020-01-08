<?php

namespace Marello\Bundle\PdfBundle\Provider\Table;

use Marello\Bundle\InvoiceBundle\Entity\Invoice;
use Marello\Bundle\InvoiceBundle\Entity\InvoiceItem;
use Marello\Bundle\PdfBundle\Lib\View\Line;
use Marello\Bundle\PdfBundle\Lib\View\Table;
use Marello\Bundle\PdfBundle\Provider\TableProviderInterface;
use Marello\Bundle\PdfBundle\Provider\TableSizeProvider;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class InvoiceTableProvider implements TableProviderInterface
{
    protected $tableSizeProvider;

    protected $firstPage = true;

    public function __construct(TableSizeProvider $tableSizeProvider)
    {
        $this->tableSizeProvider = $tableSizeProvider;
    }

    public function supports($entity)
    {
        return $entity instanceof Invoice
            && $entity->getInvoiceType() === Invoice::INVOICE_TYPE
        ;
    }

    public function getTables($entity)
    {
        $invoiceItems = $entity->getItems();

        $table = $this->createTable($this->getEntitySalesChannel($entity));
        $tables = [$table];

        $count = count($invoiceItems);
        $i = 0;

        foreach ($invoiceItems as $invoiceItem) {
            $i++;
            $line = $this->createLine($invoiceItem, $entity);

            if ($table->fitsLine($line) === false) {
                $table->disableFooter();

                if ($table->fitsLine($line) === false
                    || $i === $count
                ) {
                    $table = $this->createTable($this->getEntitySalesChannel($entity));
                    $tables[] = $table;
                }
            }

            $table->addLine($line);
        }

        return $tables;
    }

    protected function createTable(SalesChannel $salesChannel)
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

    protected function createLine(InvoiceItem $invoiceItem, Invoice $invoice)
    {
        $line = $this->createLineObject();

        $line['productSku'] = $invoiceItem->getProductSku();
        $line['productName'] = $invoiceItem->getProductName();
        $line['quantity'] = $invoiceItem->getQuantity();
        $line['price'] = $invoiceItem->getPrice();
        $line['vat'] = $invoiceItem->getTax();
        $line['total_inc_tax'] = $invoiceItem->getRowTotalInclTax();
        $line['total_ex_tax'] = $invoiceItem->getRowTotalExclTax();

        return $line;
    }

    protected function createLineObject()
    {
        return new Line([
            'productSku',
            'productName',
            'quantity',
            'price',
            'vat',
            'total_inc_tax',
            'total_ex_tax',
        ]);
    }

    protected function wrapLine($text, SalesChannel $salesChannel)
    {
        $text = str_replace(['<br>', '<br/>', '<br />'], "\n", $text);
        $text = str_replace(["\r\n"], "\n", $text);
        $text = str_replace("\r", "\n", $text);
        $text = wordwrap($text, $this->tableSizeProvider->getMaxTextWidth($salesChannel), "\n", true);

        return explode("\n", $text);
    }

    protected function getEntitySalesChannel(Invoice $entity)
    {
        return $entity->getSalesChannel();
    }
}
