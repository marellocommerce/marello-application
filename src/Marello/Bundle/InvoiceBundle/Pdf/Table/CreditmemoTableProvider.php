<?php

namespace Marello\Bundle\InvoiceBundle\Pdf\Table;

use Marello\Bundle\InvoiceBundle\Entity\Creditmemo;
use Marello\Bundle\InvoiceBundle\Entity\CreditmemoItem;
use Marello\Bundle\PdfBundle\Lib\View\Line;
use Marello\Bundle\PdfBundle\Lib\View\Table;
use Marello\Bundle\PdfBundle\Provider\TableProviderInterface;
use Marello\Bundle\PdfBundle\Provider\TableSizeProvider;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class CreditmemoTableProvider implements TableProviderInterface
{
    /**
     * @var TableSizeProvider
     */
    protected $tableSizeProvider;

    /**
     * @var bool
     */
    protected $firstPage = true;

    /**
     * @param TableSizeProvider $tableSizeProvider
     */
    public function __construct(TableSizeProvider $tableSizeProvider)
    {
        $this->tableSizeProvider = $tableSizeProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($entity)
    {
        return $entity instanceof Creditmemo
            && $entity->getInvoiceType() === Creditmemo::CREDITMEMO_TYPE
            ;
    }

    /**
     * {@inheritDoc}
     */
    public function getTables($entity)
    {
        /** @var Creditmemo $entity */
        $invoiceItems = $entity->getItems();

        $table = $this->createTable($this->getEntitySalesChannel($entity));
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
                    $table = $this->createTable($this->getEntitySalesChannel($entity));
                    $tables[] = $table;
                }
            }

            $table->addLine($line);
        }

        return $tables;
    }

    /**
     * @param SalesChannel $salesChannel
     * @return Table
     */
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

    /**
     * @param CreditmemoItem $invoiceItem
     * @return Line
     */
    protected function createLine(CreditmemoItem $invoiceItem)
    {
        $line = $this->createLineObject();

        $line['productSku'] = $invoiceItem->getProductSku();
        $line['productName'] = $invoiceItem->getProductName();
        $line['quantity'] = $invoiceItem->getQuantity();
        $line['productUnit'] = $invoiceItem->getProductUnit();
        $line['price'] = $invoiceItem->getPrice();
        $line['vat'] = $invoiceItem->getTax();
        $line['total_inc_tax'] = $invoiceItem->getRowTotalInclTax();
        $line['total_ex_tax'] = $invoiceItem->getRowTotalExclTax();

        return $line;
    }

    /**
     * @return Line
     */
    protected function createLineObject()
    {
        return new Line([
            'productSku',
            'productName',
            'quantity',
            'productUnit',
            'price',
            'vat',
            'total_inc_tax',
            'total_ex_tax',
        ]);
    }

    /**
     * @param $text
     * @param SalesChannel $salesChannel
     * @return array
     */
    protected function wrapLine($text, SalesChannel $salesChannel)
    {
        $text = str_replace(['<br>', '<br/>', '<br />'], "\n", $text);
        $text = str_replace(["\r\n"], "\n", $text);
        $text = str_replace("\r", "\n", $text);
        $text = wordwrap($text, $this->tableSizeProvider->getMaxTextWidth($salesChannel), "\n", true);

        return explode("\n", $text);
    }

    /**
     * @param Creditmemo $entity
     * @return SalesChannel
     */
    protected function getEntitySalesChannel(Creditmemo $entity)
    {
        return $entity->getSalesChannel();
    }
}
