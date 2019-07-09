<?php

namespace Marello\Bundle\PdfBundle\Provider\Table;

use Marello\Bundle\InvoiceBundle\Entity\Invoice;
use Marello\Bundle\InvoiceBundle\Entity\InvoiceItem;
use Marello\Bundle\PdfBundle\Lib\View\Line;
use Marello\Bundle\PdfBundle\Lib\View\Table;
use Marello\Bundle\PdfBundle\Provider\TableProviderInterface;

class InvoiceTableProvider implements TableProviderInterface
{
    protected $maxTextWidth;

    protected $maxHeight;

    protected $firstPageInfoHeight;

    protected $lastPageInfoHeight;

    protected $firstPage = true;

    public function __construct($maxTextWidth, $maxHeight, $firstPageInfoHeight, $lastPageInfoHeight)
    {
        $this->maxTextWidth = $maxTextWidth;
        $this->maxHeight = $maxHeight;
        $this->firstPageInfoHeight = $firstPageInfoHeight;
        $this->lastPageInfoHeight = $lastPageInfoHeight;
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

        $table = $this->createTable();
        $tables = [$table];

        $count = count($invoiceItems);
        $i = 0;

        foreach ($invoiceItems as $invoiceItem) {
            $i++;
            $line = $this->createLine($invoiceItem);

            if ($table->fitsLine($line) === false) {
                $table->disableFooter();

                if ($table->fitsLine($line) === false
                    || $i === $count
                ) {
                    $table = $this->createTable();
                    $tables[] = $table;
                }
            }

            $table->addLine($line);
        }

        return $tables;
    }

    protected function createTable()
    {
        $table = new Table($this->maxHeight, $this->firstPageInfoHeight, $this->lastPageInfoHeight);

        if ($this->firstPage) {
            $this->firstPage = false;
        } else {
            $table->disableHeader();
        }

        return $table;
    }

    protected function createLine(InvoiceItem $invoiceItem)
    {
        $line = $this->createLineObject();

        $description = sprintf('%s - %s', $invoiceItem->getProductSku(), $invoiceItem->getProductName());

        $line['description'] = $this->wrapLine($description);
        $line['quantity'] = $invoiceItem->getQuantity();
        $line['price'] = $invoiceItem->getPrice();
        $line['discount'] = $invoiceItem->getDiscountAmount();
        $line['tax'] = $invoiceItem->getTax();
        $line['total_inc_tax'] = $invoiceItem->getRowTotalInclTax();
        $line['total_ex_tax'] = $invoiceItem->getRowTotalExclTax();

        return $line;
    }

    protected function createLineObject()
    {
        return new Line([
            'description',
            'quantity',
            'price',
            'discount',
            'tax',
            'total_inc_tax',
            'total_ex_tax',
        ]);
    }

    protected function wrapLine($text)
    {
        $text = str_replace(['<br>', '<br/>', '<br />'], "\n", $text);
        $text = str_replace(["\r\n"], "\n", $text);
        $text = str_replace("\r", "\n", $text);
        $text = wordwrap($text, $this->maxTextWidth, "\n", true);

        return explode("\n", $text);
    }
}
